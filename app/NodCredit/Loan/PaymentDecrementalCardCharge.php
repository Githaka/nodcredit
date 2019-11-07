<?php

namespace App\NodCredit\Loan;


use App\BillingLog;
use App\NodCredit\Loan\Exceptions\CardDoesNotBelongsToUserException;
use App\NodCredit\Loan\Exceptions\PaymentAmountException;
use App\NodCredit\Loan\Exceptions\PaymentChargeException;
use App\Paystack\Exceptions\CheckAuthBrandException;
use App\Paystack\PaystackApi;
use App\TransactionLog;
use App\UserCard;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class PaymentDecrementalCardCharge
{

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var UserCard[]
     */
    private $cards;

    private $chargedAmount = 0;

    private $requiredAmount = 0;

    private $tryAmount;

    private $requestCount = 0;

    private $currentLevel = 1;

    /**
     * @var PaystackApi
     */
    private $paystackApi;

    /**
     * PaymentDecrementalCardCharge constructor.
     * @param Payment $payment
     * @param DecrementalConfig $config
     */
    public function __construct(Payment $payment, DecrementalConfig $config = null)
    {
        $this->payment = $payment;

        $this->config = $config ?: new DecrementalConfig();

        $this->paystackApi = app(PaystackApi::class);

        $this->cards = $this->payment->getUser()->getCardsForChargingBySystem();

        $this->requiredAmount = $this->payment->getAmount();

        $this->resetTryAmount();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function charge()
    {
        if ($this->payment->isPaid()) {
            throw new \Exception('Payment is paid');
        }

        if (! $this->cards->count()) {
            throw new \Exception('No reusable cards');
        }

        foreach ($this->cards as $card) {

            // Skip paused cards, but try to charge cards which have funds by checking check auth endpoint
            if ($card->isChargingPaused() AND ! $this->paystackApi->supportCheckAuthBrand($card->brand)) {
                continue;
            }

            try {
                $this->chargeCard($card);
            }
            catch (CardDoesNotBelongsToUserException $exception) {
                continue;
            }
            catch (PaymentChargeException $exception) {
                continue;
            }

        }

        return $this;
    }

    public function chargeCard(UserCard $card)
    {
        while (
            $this->tryAmount > $this->config->getMinAmount()
            AND $this->remainingAmount() > 0
            AND ! $this->isReachedLevel()
            AND ! $card->isDisabled()
        ) {

            $amount = $this->tryAmount;

            if ($amount > $this->remainingAmount()) {
                $amount = $this->remainingAmount();
            }

            if (! $this->chargeCardByAmount($card, $amount)) {
                $this->decreaseTryAmount();

                continue;
            }

            // Create part payment
            $this->payment->createPartPaymentAndDeductAmount($amount);

            $this->chargedAmount += $amount;

            if ($this->requestCount > 200) {
                break;
            }
        }

        $this->resetTryAmount();
        $this->resetCurrentLevel();
    }

    /**
     * @param UserCard $card
     * @param $amount
     * @return bool
     * @throws CardDoesNotBelongsToUserException
     * @throws PaymentAmountException
     * @throws PaymentChargeException
     */
    public function chargeCardByAmount(UserCard $card, $amount)
    {

        if ($card->user_id !== $this->payment->getUser()->id) {
            throw new CardDoesNotBelongsToUserException("Card [{$card->id}] does not belongs to user [{$this->payment->getUser()->id}]");
        }

        if ($amount <= 0) {
            throw new PaymentAmountException("[{$this->payment->getId()}] Charge amount is '{$amount}' <= 0. It must be more than 0.");
        }

        if ($card->isChargingPaused() AND ! $this->paystackApi->supportCheckAuthBrand($card->brand)) {
            throw new PaymentChargeException("Card [{$card->id}] is paused and card brand [{$card->brand}] does not support check auth request.");
        }

        $this->requestCount++;

        $chargeAmountInKobo = (int) ($amount * 100);

        $cardEmail = $card->email ?: $this->payment->getUser()->email;

        // Check card. Support: VISA and MASTERCARD
        try {
            $isChargeable = $this->paystackApi->isChargeable($chargeAmountInKobo, $card->auth_code, $cardEmail, $card->brand);
        }
        catch (CheckAuthBrandException $exception) {
            // Try to charge
            $isChargeable = true;
        }

        if (! $isChargeable) {
            return false;
        }

        // Continue charging
        $fields = [
            'email' =>  $cardEmail,
            'amount' => $chargeAmountInKobo,
            'authorization_code' => $card->auth_code,
            'metadata' => ['custom_fields' => ['payment_for' => 'loan_repayment', 'payment_id' => $this->payment->getId()]]
        ];

        // Charge card
        try {
            $response = $this->paystackApi->chargeAuthorization($fields);
        }
        catch (ClientException $exception) {
            $response = json_decode($exception->getResponse()->getBody()->getContents());
            $message = $response ? $response->message : 'Payment error. Be sure that you have enough balance.';

            $this->addFailedLogs($amount, $message, $response, $card);

            $card->disableIfMessage($message);

            return false;
        }
        catch (\Exception $exception) {
            $this->addFailedLogs($amount, 'Payment gateway error.', $fields, $card);

            return false;
        }

        // Failed
        if (! $response->status OR $response->data->status !== 'success') {
            $message = $response->message ?: 'Payment error. Be sure that you have enough balance.';
            $gatewayResponse = object_get($response, 'data.gateway_response', null);

            $this->addFailedLogs($amount, $message, $response, $card);

            $card->disableIfMessage($message);
            $card->disableIfMessage($gatewayResponse);

            $card->checkAndUpdateChargingPause();

            return false;
        }

        // TODO: cover case when charged amount is less using exception and try catch in clients
        if ((int) $response->data->amount < (int) $chargeAmountInKobo) {
            $this->addFailedLogs("Amount is {$chargeAmountInKobo} kobo, but returned amount by API is {$response->data->amount} kobo, ", $response, $card);

            return false;
        }

        // Successful
        $this->addSuccessfulLogs($amount, $response->message, $response, $card);

        return true;
    }

    private function remainingAmount()
    {
        return $this->requiredAmount - $this->chargedAmount;
    }

    private function decreaseTryAmount(): self
    {
        $this->tryAmount = (int) ($this->tryAmount * (100 - $this->config->getDecreasePercent()) / 100);
        $this->tryAmount = $this->floatValue($this->tryAmount);

        $this->currentLevel++;

        return $this;
    }

    private function resetTryAmount(): self
    {
        $this->tryAmount = $this->payment->getAmount() * $this->config->getStartPercent() / 100;

        return $this;
    }

    private function resetCurrentLevel(): self
    {
        $this->currentLevel = 1;

        return $this;
    }

    private function isReachedLevel(): bool
    {
        // infinite
        if ($this->config->getLevel() < 1) {
            return false;
        }

        return $this->currentLevel > $this->config->getLevel();
    }

    private function addFailedLogs($amount, string $message = '', $payload = null, UserCard $card = null)
    {
        BillingLog::create(['loan_payment_id' => $this->payment->getId(), 'info' => $message]);

        $transaction = TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => null,
            'user_id' => $this->payment->getUser()->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_FAILED,
            'model' => 'LoanPayment',
            'model_id' => $this->payment->getId(),
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
            'pay_for' => 'Loan repayment'
        ]);

        $this->log("Failed to charge N {$amount}. Transaction ID [{$transaction->id}]");
    }

    private function addSuccessfulLogs($amount, string $message = '', $payload = null, UserCard $card = null)
    {
        BillingLog::create(['loan_payment_id' => $this->payment->getId(), 'info' => $message]);

        $transaction = TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => null,
            'user_id' => $this->payment->getUser()->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_SUCCESSFUL,
            'model' => 'LoanPayment',
            'model_id' => $this->payment->getId(),
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
            'pay_for' => 'Loan repayment'
        ]);


        $this->log("Successful charge N {$amount}. Transaction ID [{$transaction->id}]. Level: {$this->currentLevel}");
    }

    private function log(string $message)
    {
        $message = "[{$this->payment->getId()}] Request count: {$this->requestCount}. " . $message;

        Log::channel('loan-decremental-charge')->info($message);
    }

    private function floatValue($value)
    {
        return (float) $value;
    }
}
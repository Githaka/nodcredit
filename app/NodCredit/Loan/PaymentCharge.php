<?php
namespace App\NodCredit\Loan;

use App\BillingLog;
use App\Events\SendMessage;
use App\FailedBilling;
use App\Message;
use App\NodCredit\Loan\Exceptions\CardDoesNotBelongsToUserException;
use App\NodCredit\Loan\Exceptions\PaymentChargeException;
use App\NodCredit\Loan\Exceptions\UserHasNoCardException;
use App\Paystack\Exceptions\CheckAuthBrandException;
use App\Paystack\Exceptions\CheckAuthException;
use App\Paystack\PaystackApi;
use App\TransactionLog;
use App\User;
use App\UserCard;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PaymentCharge
{

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserCard[]
     * @var Collection
     */
    private $cards;

    /**
     * @var PaystackApi
     */
    private $paystackApi;

    /**
     * PaymentCharge constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;

        $this->user = $payment->getUser();

        $this->paystackApi = app(PaystackApi::class);
    }

    /**
     * @param float|null $chargeAmount
     * @return bool
     */
    public function chargeUsingAllCards(float $chargeAmount = null): bool
    {
        $cards = $this->getCards();

        /** @var UserCard $card */
        foreach ($cards as $card) {

            // Skip paused cards, but try to charge cards which have funds by checking check auth endpoint
            if ($card->isChargingPaused() AND ! $this->paystackApi->supportCheckAuthBrand($card->brand)) {
                continue;
            }

            $charged = $this->chargeUsingCard($chargeAmount, $card);

            if ($charged) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param float|null $chargeAmount
     * @param UserCard $card
     * @param User|null $performedBy
     * @param bool $checkBeforeCharge
     * @return bool
     * @throws CardDoesNotBelongsToUserException
     * @throws PaymentChargeException
     */
    public function chargeUsingCard(float $chargeAmount = null, UserCard $card, User $performedBy = null, bool $checkBeforeCharge = true): bool
    {
        if ($card->user_id !== $this->getUser()->id) {
            throw new CardDoesNotBelongsToUserException("Card [{$card->id}] does not belongs to user [{$this->getPayment()->getUser()->id}]");
        }

        if ($this->getPayment()->isPaid()) {
            throw new PaymentChargeException('Payment is already paid');
        }

        if (! $chargeAmount) {
            $chargeAmount = $this->getPayment()->getAmount();
        }

        $chargeAmountInKobo = (int) ($chargeAmount * 100);

        $cardEmail = $card->email ?: $this->getUser()->email;

        // Check card. Support: VISA and MASTERCARD
        if ($checkBeforeCharge) {
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
        }

        // Continue charging
        $fields = [
            'email' =>  $cardEmail,
            'amount' => $chargeAmountInKobo,
            'authorization_code' => $card->auth_code,
            'metadata' => ['custom_fields' => ['payment_for' => 'loan_repayment', 'payment_id' => $this->getPayment()->getId()]]
        ];

        // Charge card
        try {
            $response = $this->paystackApi->chargeAuthorization($fields);
        }
        catch (ClientException $exception) {
            $response = json_decode($exception->getResponse()->getBody()->getContents());
            $message = $response ? $response->message : 'Payment error. Be sure that you have enough balance.';

            $this->failedLog($chargeAmount, $message, $response, $card, $performedBy);

            $card->disableIfMessage($message);

            return false;
        }
        catch (\Exception $exception) {
            $this->failedLog($chargeAmount, 'Payment gateway error.', $fields, $card, $performedBy);

            return false;
        }

        // Failed
        if (! $response->status OR $response->data->status !== 'success') {
            $message = $response->message ?: 'Payment error. Be sure that you have enough balance.';
            $gatewayResponse = object_get($response, 'data.gateway_response', null);

            $this->failedLog($chargeAmount, $message, $response, $card, $performedBy);

            $card->disableIfMessage($message);
            $card->disableIfMessage($gatewayResponse);

            $card->checkAndUpdateChargingPause();

            return false;
        }

        if ((int) $response->data->amount < (int) $chargeAmountInKobo) {
            $this->criticalLog("Amount is {$chargeAmountInKobo} kobo, but returned amount by API is {$response->data->amount} kobo, ", $response);
        }

        // Successful
        $this->successfulLog($chargeAmount, $response->message, $response, $card, $performedBy);

        // Full payment amount
        if ($chargeAmount === $this->getPayment()->getAmount()) {
            $this->getPayment()->paidAndCheckLoan();
        }
        // Part of payment amount
        else {
            $this->getPayment()->createPartPaymentAndDeductAmount($chargeAmount);
        }

        $this->sendMessage($chargeAmount);

        return true;
    }

    private function failedLog($amount, string $message, $payload = null, UserCard $card = null, User $performedBy = null)
    {
        TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => $performedBy ? $performedBy->id : null,
            'user_id' => $this->getPayment()->getUser()->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_FAILED,
            'model' => 'LoanPayment',
            'model_id' => $this->getPayment()->getId(),
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
            'pay_for' => 'Loan repayment'
        ]);

        Log::channel('loan-payment-charge')->info("Payment [{$this->getPayment()->getId()}]. Failed charge: N{$amount}.", [serialize($payload)]);
    }

    private function successfulLog($amount, string $message = 'successful', $payload = null, UserCard $card = null, User $performedBy = null)
    {
        TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => $performedBy ? $performedBy->id : null,
            'user_id' => $this->getUser()->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_SUCCESSFUL,
            'model' => 'LoanPayment',
            'model_id' => $this->getPayment()->getId(),
            'pay_for' => 'Loan repayment',
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
        ]);

        Log::channel('loan-payment-charge')->info("Payment [{$this->getPayment()->getId()}]. Successful charge: N{$amount}.", [serialize($payload)]);
    }

    private function criticalLog(string $message = '', $payload = null)
    {
        $context = [
            'payload' => serialize($payload),
            'loan_payment_id' => $this->getPayment()->getId()
        ];

        Log::channel('loan-payment-charge')->critical($message, $context);
    }

    private function sendMessage(float $amount)
    {
        $message = Message::create([
            'message' => 'Your loan-repayment was successful. Amount billed: N' . number_format($amount,2),
            'message_type' => 'email',
            'subject' => 'Loan Re-payment Alert',
            'user_id' => $this->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));
    }

    /**
     * @return Payment
     */
    private function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return User
     */
    private function getUser()
    {
        return $this->user;
    }

    /**
     * @return Collection
     */
    public function getCards()
    {
        if (! $this->cards) {
            $this->cards = $this->getUser()->getCardsForChargingBySystem();
        }

        return $this->cards;
    }
}
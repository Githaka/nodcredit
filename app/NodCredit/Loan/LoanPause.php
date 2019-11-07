<?php

namespace App\NodCredit\Loan;

use App\NodCredit\Account\User;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Exceptions\ApplicationHasNoPaymentException;
use App\NodCredit\Loan\Exceptions\ApplicationPauseException;
use App\NodCredit\Loan\Exceptions\CardDoesNotBelongsToUserException;
use App\Paystack\Exceptions\CheckAuthBrandException;
use App\Paystack\PaystackApi;
use App\TransactionLog;
use App\UserCard;
use App\User as UserModel;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Collection;

class LoanPause
{
    /** percent */
    const PAUSE_PRICE = 0.15;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var PaymentCollection
     */
    private $scheduledPayments;

    /**
     * @var \App\User
     */
    private $user;

    /**
     * @var Collection
     */
    private $cards;

    /**
     * @var PaystackApi
     */
    private $paystackApi;

    /**
     * LoanPause constructor.
     * @param \App\NodCredit\Loan\Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;

        $this->user = $application->getUser();

        $this->accountUser = $application->getAccountUser();

        $this->scheduledPayments = $application->getScheduledPayments();

        $this->cards = $this->user->getCardsForChargingBySystem();

        $this->paystackApi = app(PaystackApi::class);
    }

    /**
     * @return bool
     * @throws ApplicationPauseException
     */
    public function pauseUsingAllCards(): bool
    {
        /** @var UserCard $card */
        foreach ($this->cards as $card) {

            // Skip paused cards, but try to charge cards which have funds by checking check auth endpoint
            if ($card->isChargingPaused() AND ! $this->paystackApi->supportCheckAuthBrand($card->brand)) {
                continue;
            }

            try {
                $paused = $this->pauseUsingCard($card);
            }
            catch (CardDoesNotBelongsToUserException $exception) {
                continue;
            }


            if ($paused) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param UserCard $card
     * @return bool
     * @throws ApplicationPauseException
     * @throws CardDoesNotBelongsToUserException
     */
    public function pauseUsingCard(UserCard $card): bool
    {
        if ($card->user_id !== $this->user->id) {
            throw new CardDoesNotBelongsToUserException("Card [{$card->id}] does not belongs to user [{$this->user->id}]");
        }

        $charged = $this->chargeCard($card);

        if (! $charged) {
            return false;
        }

        $this->shiftDueAtForMonth();

        return true;
    }

    /**
     * @param UserCard $card
     * @return bool
     * @throws ApplicationPauseException
     * @throws CardDoesNotBelongsToUserException
     */
    public function pauseByUserUsingCard(UserCard $card): bool
    {
        if ($card->user_id !== $this->user->id) {
            throw new CardDoesNotBelongsToUserException("Card [{$card->id}] does not belongs to user [{$this->user->id}]");
        }

        if (! $this->application->canPauseByUser()) {
            throw new ApplicationPauseException('Loan pause limit is exceeded.');
        }

        if ($this->accountUser->isDefaulter()) {
            throw new ApplicationPauseException('Loan is overdue and can not be paused.');
        }

        $charged = $this->chargeCard($card, null, $this->user, false);

        if (! $charged) {
            return false;
        }

        $this->shiftDueAtForMonth();

        $this->application->setPausedByUser();

        return true;
    }

    /**
     * @param UserCard $card
     * @param float|null $amount
     * @param UserModel|null $performedBy
     * @param bool $checkBeforeCharge
     * @return bool
     * @throws ApplicationHasNoPaymentException
     */
    public function chargeCard(UserCard $card, float $amount = null, UserModel $performedBy = null, bool $checkBeforeCharge = true): bool
    {
        /** @var Payment $firstPayment */
        $firstPayment = $this->scheduledPayments->first();

        if (! $firstPayment) {
            throw new ApplicationHasNoPaymentException('Loan Application has no scheduled payments.');
        }

        $chargeAmount = $amount ?: $firstPayment->getAmount() * static::PAUSE_PRICE;

        $chargeAmountInKobo = (int) ($chargeAmount * 100);

        $cardEmail = $card->email ?: $this->user->email;

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
            'metadata' => ['custom_fields' => ['payment_for' => 'pausing_of_loan', 'payment_id' => $firstPayment->getId()]]
        ];

        // Charge card
        try {
            $response = $this->paystackApi->chargeAuthorization($fields);
        }
        catch (ClientException $exception) {
            $response = json_decode($exception->getResponse()->getBody()->getContents());

            $message = $response ? $response->message : 'Payment error. Be sure that you have enough balance.';

            $this->createFailedTransactionLog($chargeAmount, $firstPayment, $message, $response, $card, $performedBy);

            $card->disableIfMessage($message);

            return false;
        }
        catch (\Exception $exception) {
            $this->createFailedTransactionLog($chargeAmount, $firstPayment, 'Payment gateway error.', $fields, $card, $performedBy);

            return false;
        }

        // Failed
        if (! $response->status OR $response->data->status !== 'success') {
            $message = $response->message ?: 'Payment error. Be sure that you have enough balance.';
            $gatewayResponse = object_get($response, 'data.gateway_response', null);

            $this->createFailedTransactionLog($chargeAmount, $firstPayment, $message, $response, $card, $performedBy);

            $card->disableIfMessage($message);
            $card->disableIfMessage($gatewayResponse);

            $card->checkAndUpdateChargingPause();

            return false;
        }

        // Successful
        $this->createSuccessfulTransactionLog($chargeAmount, $firstPayment, $response->data->gateway_response, $response, $card, $performedBy);

        return true;
    }

    public function shiftDueAtForMonth()
    {
        try {
            /** @var Payment $payment */
            foreach($this->scheduledPayments->all() as $payment) {
                $payment->shiftDueAtForMonth();
            }
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    public function createFailedTransactionLog($amount, Payment $payment, string $message, $payload = null, UserCard $card = null, UserModel $performedBy = null)
    {
        return TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => $performedBy ? $performedBy->id : null,
            'user_id' => $this->user->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_FAILED,
            'model' => 'LoanPayment',
            'model_id' => $payment->getId(),
            'pay_for' => 'Charge for loan pause',
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
        ]);
    }

    public function createSuccessfulTransactionLog($amount, Payment $payment, string $message, $payload = null, UserCard $card = null, UserModel $performedBy = null)
    {
        return TransactionLog::create([
            'trans_type' => 'debit',
            'payload' => serialize($payload),
            'amount' => $amount,
            'performed_by' => $performedBy ? $performedBy->id : null,
            'user_id' => $this->user->id,
            'card_id' => $card ? $card->id : null,
            'status' => TransactionLog::STATUS_SUCCESSFUL,
            'model' => 'LoanPayment',
            'model_id' => $payment->getId(),
            'pay_for' => 'Charge for loan pause',
            'response_message' => $message,
            'gateway_response' => object_get($payload, 'data.gateway_response', null),
        ]);
    }
}
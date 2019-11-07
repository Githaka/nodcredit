<?php

namespace App\NodCredit\Investment;

use App\NodCredit\Account\User;
use App\NodCredit\Investment\Exceptions\PayoutException;
use App\Paystack\Exceptions\CreateTransferRecipientException;
use App\Paystack\Exceptions\TransferException;
use App\Paystack\PaystackApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;

class Payout
{

    /**
     * @var PaystackApi
     */
    private $paystackApi;

    /**
     * @param PartialLiquidation $partialLiquidation
     * @return bool
     * @throws CreateTransferRecipientException
     * @throws PayoutException
     * @throws TransferException
     * @throws \Exception
     */
    public static function partialPayout(PartialLiquidation $partialLiquidation): bool
    {
        if ($partialLiquidation->isPaid()) {
            throw new PayoutException('Partial Liquidation is already paid out.');
        }

        $payout = new static();

        $user = $partialLiquidation->getInvestment()->getUser();

        $amount = $partialLiquidation->getAmount();

        try {
            $response = $payout->transfer($amount, $user, [
                'description' => "NodCredit Investment Partial Liquidation. Reason: {$partialLiquidation->getReason()}"
            ]);
        }
        catch (PayoutException $exception) {
            $payout->errorLog("[{$partialLiquidation->getId()}] Partial Liquidation [$amount] payout fail. Payout Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (CreateTransferRecipientException $exception) {
            $payout->errorLog("[{$partialLiquidation->getId()}] Partial Liquidation [$amount] payout fail. Create Transfer Recipient Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (TransferException $exception) {
            $payout->errorLog("[{$partialLiquidation->getId()}] Partial Liquidation [$amount] payout fail. Transfer Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (\Exception $exception) {
            $payout->errorLog("[{$partialLiquidation->getId()}] Partial Liquidation [$amount] payout fail. Exception: {$exception->getMessage()}");

            throw $exception;
        }

        $partialLiquidation->paid($response);

        $payout->infoLog("[{$partialLiquidation->getId()}] Partial Liquidation [$amount] successful payout.");

        return true;
    }

    /**
     * @param Investment $investment
     * @return bool
     * @throws CreateTransferRecipientException
     * @throws PayoutException
     * @throws TransferException
     * @throws \Exception
     */
    public static function fullPayout(Investment $investment): bool
    {
        if ($investment->isPaidOut()) {
            throw new PayoutException('Investment is already paid out.');
        }

        $payout = new static();

        $amount = $investment->getPayoutAmount();

        try {
            $response = $payout->transfer($amount, $investment->getUser(), ['description' => 'NodCredit Investment Payment']);
        }
        catch (PayoutException $exception) {
            $payout->errorLog("[{$investment->getId()}] Investment [$amount] payout fail. Payout Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (CreateTransferRecipientException $exception) {
            $payout->errorLog("[{$investment->getId()}] Investment [$amount] payout fail. Create Transfer Recipient Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (TransferException $exception) {
            $payout->errorLog("[{$investment->getId()}] Investment [$amount] payout fail. Transfer Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (\Exception $exception) {
            $payout->errorLog("[{$investment->getId()}] Investment [$amount] payout fail. Exception: {$exception->getMessage()}");

            throw $exception;
        }

        // Response status false
        if (! $response->status) {
            $investment->failedToPayout($response);

            throw new PayoutException($response->message);
        }

        // Response transfer status is not pending or success
        if (! in_array($response->data->status, ['pending', 'success'])) {
            $investment->failedToPayout($response);

            throw new PayoutException($response->message);
        }

        $investment->successPayout($response);

        $payout->infoLog("[{$investment->getId()}] Investment [$amount] payout successful.");

        return true;
    }

    /**
     * @param ProfitPayment $profitPayment
     * @return bool
     * @throws CreateTransferRecipientException
     * @throws PayoutException
     * @throws TransferException
     * @throws \Exception
     */
    public static function profitPayout(ProfitPayment $profitPayment): bool
    {
        if ($profitPayment->isPaid()) {
            throw new PayoutException('Profit is already paid out.');
        }

        $now = now();

        if ($profitPayment->getScheduledAt()->gt($now)) {
            throw new PayoutException('Scheduled date did not come.');
        }

        $payout = new static();

        $amount = $profitPayment->getPayoutAmount();

        try {
            $response = $payout->transfer($amount, $profitPayment->getUser(), [
                'description' => "NodCredit Investment Profit Payment. Period: {$profitPayment->getPeriodStart()->format('Y-m-d')} - {$profitPayment->getPeriodEnd()->format('Y-m-d')}"
            ]);
        }
        catch (PayoutException $exception) {
            $payout->errorLog("[{$profitPayment->getId()}] Profit [$amount] payout fail. Payout Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (CreateTransferRecipientException $exception) {
            $payout->errorLog("[{$profitPayment->getId()}] Profit [$amount] payout fail. Create Transfer Recipient Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (TransferException $exception) {
            $payout->errorLog("[{$profitPayment->getId()}] Profit [$amount] payout fail. Transfer Exception: {$exception->getMessage()}");

            throw $exception;
        }
        catch (\Exception $exception) {
            $payout->errorLog("[{$profitPayment->getId()}] Profit [$amount] payout fail. Exception: {$exception->getMessage()}");

            throw $exception;
        }

        $profitPayment->paid($response);

        $payout->infoLog("[{$profitPayment->getId()}] Profit [$amount] payout successful.");

        return true;
    }

    /**
     * Payout constructor.
     */
    public function __construct()
    {
        $this->paystackApi = app(PaystackApi::class);
    }

    /**
     * @param float $amount
     * @param User $user
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws PayoutException
     */
    public function transfer(float $amount, User $user, array $data = [])
    {
        if (! $user->getName()) {
            throw new PayoutException("User name is required.");
        }

        if (! $user->getAccountNumber()) {
            throw new PayoutException("Bank account number is required.");
        }

        if (! $user->getBankCode()) {
            throw new PayoutException("Bank code is required.");
        }

        return $this->paystackApi->transfer($amount, $user->getName(), $user->getAccountNumber(), $user->getBankCode(), $data);
    }

    private function log(string $level = 'info', string $message, $payload = null): self
    {
        if ($level === 'error') {
            Log::channel('investments-payouts')->error($message);
        }
        else {
            Log::channel('investments-payouts')->info($message);
        }

        return $this;
    }

    private function errorLog(string $message, $payload = null): self
    {
        return $this->log('error', $message);
    }

    private function infoLog(string $message, $payload = null): self
    {
        return $this->log('info', $message);
    }
}
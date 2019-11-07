<?php

namespace App\NodCredit\Investment\Notifications;

use App\Mail\MessageSenderMail;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\ProfitPayment;
use App\NodCredit\Message\MessageSender;
use App\User;
use Illuminate\Support\Facades\Mail;

class InvestmentProfitPaymentPaid
{

    /**
     * @var ProfitPayment
     */
    private $profitPayment;

    /**
     * @param ProfitPayment $profitPayment
     * @param string $whom
     * @return bool
     * @throws \Exception
     */
    public static function notify(ProfitPayment $profitPayment, string $whom = 'all')
    {
        if (! in_array($whom, ['all', 'user', 'admin'])) {
            throw new \Exception("Incorrect input param {$whom}");
        }

        $notifier = new static($profitPayment);

        if ($whom === 'all') {
            $notifier->notifyUser();
            $notifier->notifyAdmin();

            return true;
        }
        else if ($whom === 'user') {
            return $notifier->notifyUser();
        }
        else if ($whom === 'admin') {
            return $notifier->notifyAdmin();
        }
    }

    /**
     * InvestmentProfitPaymentPaid constructor.
     * @param ProfitPayment $profitPayment
     */
    public function __construct(ProfitPayment $profitPayment)
    {
        $this->profitPayment = $profitPayment;
        $this->investment = $profitPayment->getInvestment();
    }

    private function notifyUser()
    {
        try {
            MessageSender::send('investment-profit-payment-paid', $this->investment->getUser(), [
                '#PROFIT_PAYMENT_AMOUNT#' => Money::formatInNaira($this->profitPayment->getAmount()),
                '#PROFIT_PAYMENT_WITHHOLDING_TAX_AMOUNT#' => Money::formatInNaira($this->profitPayment->getWithholdingTaxAmount()),
                '#PROFIT_PAYMENT_WITHHOLDING_TAX_PERCENT#' => $this->profitPayment->getWithholdingTaxPercent(),
                '#PROFIT_PAYMENT_PAYOUT_AMOUNT#' => Money::formatInNaira($this->profitPayment->getPayoutAmount()),
            ]);
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    private function notifyAdmin(): bool
    {
        try {
            $mailToAdmin = new MessageSenderMail('admin-investment-profit-payment-paid', $this->investment->getUser(), [
                '#PROFIT_PAYMENT_AMOUNT#' => Money::formatInNaira($this->profitPayment->getAmount()),
                '#PROFIT_PAYMENT_WITHHOLDING_TAX_AMOUNT#' => Money::formatInNaira($this->profitPayment->getWithholdingTaxAmount()),
                '#PROFIT_PAYMENT_WITHHOLDING_TAX_PERCENT#' => $this->profitPayment->getWithholdingTaxPercent(),
                '#PROFIT_PAYMENT_PAYOUT_AMOUNT#' => Money::formatInNaira($this->profitPayment->getPayoutAmount()),
                '#INVESTMENT_URL#' => route('mainframe.investment.manage', ['id' => $this->investment->getId()])
            ]);

            $admins = User::where('role', 'admin')->get();

            Mail::to($admins)->send($mailToAdmin);
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }

}
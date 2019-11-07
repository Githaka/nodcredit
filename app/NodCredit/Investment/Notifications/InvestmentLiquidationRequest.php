<?php

namespace App\NodCredit\Investment\Notifications;

use App\Mail\MessageSenderMail;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Settings;
use App\User;
use Illuminate\Support\Facades\Mail;

class InvestmentLiquidationRequest
{

    /**
     * @var Investment
     */
    private $investment;
    /**
     * @var float
     */
    private $amount;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Investment $investment
     * @param string $whom
     * @param float $amount
     * @param string $reason
     * @return bool
     * @throws \Exception
     */
    public static function notify(Investment $investment, string $whom = 'all', float $amount, string $reason = '')
    {
        if (! in_array($whom, ['all', 'user', 'admin'])) {
            throw new \Exception("Incorrect input param {$whom}");
        }

        $notifier = new static($investment, $amount, $reason);

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
     * InvestmentLiquidationRequest constructor.
     * @param Investment $investment
     * @param float $amount
     * @param string $reason
     */
    public function __construct(Investment $investment, float $amount, string $reason = '')
    {
        $this->investment = $investment;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->settings = app(Settings::class);
    }

    private function notifyUser()
    {
        try {
            MessageSender::send('investment-liquidation-request', $this->investment->getUser(), [
                '#LIQUIDATION_REASON#' => $this->reason
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

            if ($this->amount <= $this->settings->get('investment_max_auto_payout', 400000)) {
                $payout = 'Payout is automatic.';
            }
            else {
                $payout = 'Payout is manual. Please, process this request on the Investment page.';
            }

            $mailToAdmin = new MessageSenderMail('admin-investment-liquidation-request', $this->investment->getUser(), [
                '#LIQUIDATION_AMOUNT#' => Money::formatInNaira($this->amount),
                '#LIQUIDATION_REASON#' => $this->reason,
                '#LIQUIDATION_PAYOUT#' => $payout,
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
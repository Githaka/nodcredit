<?php

namespace App\NodCredit\Investment\Notifications;

use App\Mail\MessageSenderMail;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Message\MessageSender;
use App\User;
use Illuminate\Support\Facades\Mail;

class InvestmentCompleted
{

    /**
     * @var Investment
     */
    private $investment;

    /**
     * @param Investment $investment
     * @param string $whom
     * @return bool
     * @throws \Exception
     */
    public static function notify(Investment $investment, string $whom = 'all')
    {
        if (! in_array($whom, ['all', 'user', 'admin'])) {
            throw new \Exception("Incorrect input param {$whom}");
        }

        $notifier = new static($investment);

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
     * InvestmentCompleted constructor.
     * @param Investment $investment
     */
    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    private function notifyUser()
    {
        try {
            MessageSender::send('investment-completed', $this->investment->getUser(), [
                '#INVESTMENT_AMOUNT#' => Money::formatInNaira($this->investment->getAmount()),
                '#INVESTMENT_PLAN_NAME#' => $this->investment->getPlanName(),
                '#INVESTMENT_PLAN_PERCENTAGE#' => $this->investment->getPlanPercentage(),
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
            $mailToAdmin = new MessageSenderMail('admin-investment-completed', $this->investment->getUser(), [
                '#INVESTMENT_AMOUNT#' => Money::formatInNaira($this->investment->getAmount()),
                '#INVESTMENT_PLAN_NAME#' => $this->investment->getPlanName(),
                '#INVESTMENT_PLAN_PERCENTAGE#' => $this->investment->getPlanPercentage(),
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
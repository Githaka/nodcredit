<?php

namespace App\NodCredit\Investment\Notifications;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Message\MessageSender;

class FullLiquidationPaid
{
    /**
     * @var Investment
     */
    private $investment;

    /**
     * @param Investment $investment
     * @param string $whom
     * @return mixed
     * @throws \Exception
     */
    public static function notify(Investment $investment, string $whom = 'user')
    {
        if (! in_array($whom, ['user'])) {
            throw new \Exception("Incorrect input param {$whom}");
        }

        $notifier = new static($investment);

        if ($whom === 'user') {
            return $notifier->notifyUser();
        }
    }

    /**
     * FullLiquidationPaid constructor.
     * @param Investment $investment
     */
    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    private function notifyUser()
    {
        try {
            MessageSender::send('investment-full-liquidation-paid', $this->investment->getUser(), [
                '#LIQUIDATION_AMOUNT#' => Money::formatInNaira($this->investment->getAmount())
            ]);
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}
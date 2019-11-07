<?php

namespace App\NodCredit\Investment\Notifications;

use App\Mail\MessageSenderMail;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Message\MessageSender;
use App\User;
use Illuminate\Support\Facades\Mail;

class PartialLiquidationPaid
{
    /**
     * @var PartialLiquidation
     */
    private $partialLiquidation;

    /**
     * @var Investment
     */
    private $investment;

    /**
     * @param PartialLiquidation $partialLiquidation
     * @param string $whom
     * @return mixed
     * @throws \Exception
     */
    public static function notify(PartialLiquidation $partialLiquidation, string $whom = 'user')
    {
        if (! in_array($whom, ['user'])) {
            throw new \Exception("Incorrect input param {$whom}");
        }

        $notifier = new static($partialLiquidation);

        if ($whom === 'user') {
            return $notifier->notifyUser();
        }
    }

    /**
     * PartialLiquidationPaid constructor.
     * @param PartialLiquidation $partialLiquidation
     */
    public function __construct(PartialLiquidation $partialLiquidation)
    {
        $this->partialLiquidation = $partialLiquidation;
        $this->investment = $partialLiquidation->getInvestment();
    }

    private function notifyUser()
    {
        try {
            MessageSender::send('investment-partial-liquidation-paid', $this->investment->getUser(), [
                '#LIQUIDATION_AMOUNT#' => Money::formatInNaira($this->partialLiquidation->getAmount())
            ]);
        }
        catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}
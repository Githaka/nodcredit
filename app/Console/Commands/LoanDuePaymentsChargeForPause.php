<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\LoanPause;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use App\Paystack\PaystackApi;
use App\UserCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsChargeForPause extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-charge-for-pause';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments charge for pause handler';

    /**
     * @var Template
     */
    private $messageTemplate;


    public function __construct()
    {
        parent::__construct();

        $templateKey = 'loan-due-payment-charge-for-pause';

        try {
            $this->messageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $duePayments = PaymentCollection::findNeedToChargeForPause();
        $paystackApi = app(PaystackApi::class);

        $this->log("Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            $charged = false;

            $pauseHandler = new LoanPause($payment->getApplication());

            $cards = $payment->getApplication()->getUser()->getCardsForChargingBySystem();

            $this->log("Payment [{$payment->getId()}]. Try to charge for pause using all cards. Cards count: {$cards->count()}" );

            $pausePrice = floatval($payment->getAmount() / (1 + LoanPause::PAUSE_PRICE) * LoanPause::PAUSE_PRICE);

            // Try to pause
            /** @var UserCard $card */
            foreach ($cards as $card) {

                // Skip paused cards, but try to charge cards which have funds by checking check auth endpoint
                if ($card->isChargingPaused() AND ! $paystackApi->supportCheckAuthBrand($card->brand)) {
                    continue;
                }

                try {
                    $charged = $pauseHandler->chargeCard($card, $pausePrice);
                }
                catch (\Exception $exception) {}

                if ($charged) {
                    $this->log("Payment [{$payment->getId()}]. Card [{$card->id}]. Successful charged.");
                    break;
                }

                // Failed, try next card
                $this->log("Payment [{$payment->getId()}]. Card [{$card->id}]. Failed");
            }

            // Failed
            if (! $charged) {
                continue;
            }

            // Charged. Reset flag, deduct pause price amount and send message
            $payment->setNeedToChargeForPause(false);

            $payment->deductAmount($pausePrice);

            $this->sendSuccessfulMessage($payment);
        }
    }

    private function sendSuccessfulMessage(Payment $payment)
    {
        if (! $this->messageTemplate) {
            return;
        }

        $content = $this->messageTemplate->getMessage();
        $content = UserHandler::handle($content, $payment->getUser());
        $content = ReplaceHandler::handle($content, [
            '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
            '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
            '#PAYMENT_DUE_AT#' => $payment->getDueAt()->format('Y-m-d'),
            '#PAYMENT_DUE_AT_OLD#' => $payment->getDueAt()->subMonth()->format('Y-m-d'),
        ]);

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->messageTemplate->getChannel(),
            'subject' => $this->messageTemplate->getTitle(),
            'user_id' => $payment->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));

        $this->log("Payment [{$payment->getId()}]. Send message [{$message->id}] to user");
    }

    /**
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-charge-for-pause')->info($message, $context);
    }

}

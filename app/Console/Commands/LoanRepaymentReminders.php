<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Mail\MessageSenderMail;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LoanRepaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:repayment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan Re-Payment reminders';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleReminder0Day();
        $this->handleReminder1Day();
        $this->handleReminder2Days();
        $this->handleReminder7Days();
        $this->handleReminder15Days();
    }

    private function handleReminder7Days()
    {
        $paymentsDueIn = PaymentCollection::findDueIn(7);

        $template = Template::findByKey('loan-repayment-reminder-7-days');

        /** @var Payment $payment */
        foreach ($paymentsDueIn->all() as $payment) {

            $content = $template->getMessage();
            $content = UserHandler::handle($content, $payment->getUser());
            $content = ReplaceHandler::handle($content, [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2)
            ]);

            $message = Message::create([
                'message' => $content,
                'message_type' => $template->getChannel(),
                'subject' => $template->getTitle(),
                'user_id' => $payment->getUser()->id,
                'sender' => 'system'
            ]);

            event(new SendMessage($message));
        }

    }

    private function handleReminder2Days()
    {
        $paymentsDueIn = PaymentCollection::findDueIn(2);

        $template = Template::findByKey('loan-repayment-reminder-2-days');

        /** @var Payment $payment */
        foreach ($paymentsDueIn->all() as $payment) {

            $content = $template->getMessage();
            $content = UserHandler::handle($content, $payment->getUser());
            $content = ReplaceHandler::handle($content, [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2)
            ]);

            $message = Message::create([
                'message' => $content,
                'message_type' => $template->getChannel(),
                'subject' => $template->getTitle(),
                'user_id' => $payment->getUser()->id,
                'sender' => 'system'
            ]);

            event(new SendMessage($message));
        }
    }

    private function handleReminder1Day()
    {
        $paymentsDueIn = PaymentCollection::findDueIn(1);

        $template = Template::findByKey('loan-repayment-reminder-1-day');

        /** @var Payment $payment */
        foreach ($paymentsDueIn->all() as $payment) {

            $content = $template->getMessage();
            $content = UserHandler::handle($content, $payment->getUser());
            $content = ReplaceHandler::handle($content, [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2)
            ]);

            $message = Message::create([
                'message' => $content,
                'message_type' => $template->getChannel(),
                'subject' => $template->getTitle(),
                'user_id' => $payment->getUser()->id,
                'sender' => 'system'
            ]);

            event(new SendMessage($message));
        }
    }

    private function handleReminder0Day()
    {
        $paymentsDueIn = PaymentCollection::findDueIn(0);

        $template = Template::findByKey('loan-repayment-reminder-0-day');

        /** @var Payment $payment */
        foreach ($paymentsDueIn->all() as $payment) {

            $content = $template->getMessage();
            $content = UserHandler::handle($content, $payment->getUser());
            $content = ReplaceHandler::handle($content, [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2)
            ]);

            $message = Message::create([
                'message' => $content,
                'message_type' => $template->getChannel(),
                'subject' => $template->getTitle(),
                'user_id' => $payment->getUser()->id,
                'sender' => 'system'
            ]);

            event(new SendMessage($message));
        }
    }


    private function handleReminder15Days()
    {
        $paymentsDueIn = PaymentCollection::findDueIn(15);

        /** @var Payment $payment */
        foreach ($paymentsDueIn->all() as $payment) {
            MessageSender::send('loan-repayment-reminder-15-days', $payment->getAccountUser(), [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2)
            ]);
        }
    }
}

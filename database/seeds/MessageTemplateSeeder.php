<?php

use App\MessageTemplate;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateFactory;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MessageTemplate::truncate();

        $this->seedReminders();

        TemplateFactory::create([
            'key' => 'loan-due-payment-charge-and-pause',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan payment is due. We charge and pause for a month',
            'message' => 'Dear #USER_NAME#, your loan payment is due. Payment amount: #PAYMENT_AMOUNT#. Loan amount: #LOAN_AMOUNT#',
        ]);

        TemplateFactory::create([
            'key' => 'loan-due-payment-increase-and-pause',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan payment is due. We increase and pause for a month',
            'message' => 'Dear #USER_NAME#, your loan payment is due. Payment amount: #PAYMENT_AMOUNT#. Loan amount: #LOAN_AMOUNT#',
        ]);

        TemplateFactory::create([
            'key' => 'loan-due-payment-twice',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'We start to apply daily 1% interest on outstanding.',
            'message' => 'Dear #USER_NAME#, we start to apply daily 1% interest on outstanding. Payment amount: #PAYMENT_AMOUNT#. Loan amount: #LOAN_AMOUNT#',
        ]);
    }

    private function seedReminders()
    {
        $reminder7days = [
            'key' => 'loan-repayment-reminder-7-days',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan Re-payment Reminder',
            'message' => 'Dear #USER_NAME#, your loan re-payment of #PAYMENT_AMOUNT# is due in 7 days. Please ensure your #USER_BANK_NAME# account is funded.',
        ];
        TemplateFactory::create($reminder7days);

        $reminder2days = [
            'key' => 'loan-repayment-reminder-2-days',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan Re-payment Reminder',
            'message' => 'Dear #USER_NAME#, your loan re-payment of #PAYMENT_AMOUNT# is due in 2 days. Please ensure your #USER_BANK_NAME# account is funded.',
        ];
        TemplateFactory::create($reminder2days);

        $reminder1day = [
            'key' => 'loan-repayment-reminder-1-day',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan Re-payment Reminder',
            'message' => 'Dear #USER_NAME#, your loan re-payment of #PAYMENT_AMOUNT# is due tomorrow. Please ensure your #USER_BANK_NAME# account is funded.',
        ];
        TemplateFactory::create($reminder1day);

        $reminder0day = [
            'key' => 'loan-repayment-reminder-0-day',
            'channel' => Template::CHANNEL_BOTH,
            'title' => 'Loan Re-payment Reminder',
            'message' => 'Dear #USER_NAME#, your loan re-payment of #PAYMENT_AMOUNT# is due today. Please ensure your #USER_BANK_NAME# account is funded.',
        ];
        TemplateFactory::create($reminder0day);

    }
}

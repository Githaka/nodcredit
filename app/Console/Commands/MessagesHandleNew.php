<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;

class MessagesHandleNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:handle-new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send new messages';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $messages = Message::where('status', Message::STATUS_NEW)
            ->where('created_at', '<', now()->subMinutes(5))
            ->get()
        ;

        foreach ($messages as $message) {
            event(new SendMessage($message));
        }
    }

}

<?php

namespace App\Listeners;

use App\Events\SendMessage;
use App\Mail\MessageTemplateMail;
use App\Message;
use App\Models\UserDevice;
use App\Services\Sling\SlingApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OnSendMessage implements ShouldQueue
{
    /** @var SlingApi  */
    private $smsProvider;

    /** @var string */
    private $logChannel = 'messages';

    /**
     * OnSendMessage constructor.
     */
    public function __construct()
    {
        $this->smsProvider = app(SlingApi::class);
    }

    /**
     * @param SendMessage $event
     * @return bool|void
     */
    public function handle(SendMessage $event)
    {
        if ($event->message->status !== Message::STATUS_NEW) {
            $this->logInfo("[{$event->message->id}] message status must be [".Message::STATUS_NEW."]");

            return;
        }

        if ($event->message->message_type === 'sms') {
            $this->sendSMSMessage($event->message);
        }
        else if ($event->message->message_type === 'email') {
            $this->sendEmailMessage($event->message);
        }
        else if ($event->message->message_type === 'both') {
            $this->sendEmailMessage($event->message);
            $this->sendSMSMessage($event->message);
        }

        $event->message->status = Message::STATUS_SENT;
        $event->message->save();

        $this->sendPushMessage($event->message);
    }

    protected function sendEmailMessage($message)
    {

        $to = $message->user->email;
        $mail = new MessageTemplateMail($message);

        $this->logInfo("[{$message->id}] Send email to {$to}.");

        try {
            Mail::to($to)->send($mail);
        }
        catch (\Exception $exception) {
            $this->logError("[{$message->id}] SEND EMAIL ERROR: {$exception->getMessage()}");

            return false;
        }

        return true;
    }

    protected function sendSMSMessage($message)
    {
        $to = $message->user->phone;

        $text = strip_tags($message->message);
        $text = trim($text);

        $this->logInfo("[{$message->id}] Send SMS to {$to}.");

        try {
            $this->smsProvider->sendSms($to, $text);
        }
        catch (\Exception $exception) {
            $this->logError("[{$message->id}] SEND SMS ERROR: {$exception->getMessage()}");

            return false;
        }

        return true;
    }

    protected function sendPushMessage($message)
    {
        $devicesId = UserDevice::where('user_id', $message->user->id)->get()->pluck('device_id')->toArray();

        if (! count($devicesId)) {
            return false;
        }

        $this->logInfo("[{$message->id}] Send Push message to: " . implode(',', $devicesId));

        $url = route('api.v1.messages.content', ['id' => $message->id]);

        try {
            \OneSignal::sendNotificationToUser(
                $url,
                $devicesId,
                null,
                null,
                null,
                null,
                $message->subject
            );
        }
        catch (\Exception $exception) {
            $this->logError("[{$message->id}] SEND PUSH ERROR: {$exception->getMessage()}");

            return false;
        }

        return true;
    }


    private function logInfo($message, array $context = []): self
    {
        return $this->log('info', $message, $context);
    }

    private function logError(string $message = '', array $context = []): self
    {
        return $this->log('error', $message, $context);
    }

    private function log(string $type, string $message, array $context = []): self
    {
        Log::channel($this->logChannel)->{$type}($message, $context);

        return $this;
    }

}

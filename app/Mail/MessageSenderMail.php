<?php

namespace App\Mail;

use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageSenderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $templateKey;

    /**
     * @var array
     */
    private $data;

    /**
     * MessageSenderMail constructor.
     * @param $templateKey
     * @param \App\NodCredit\Account\User $user
     * @param array $data
     */
    public function __construct($templateKey, \App\NodCredit\Account\User $user, array $data = [])
    {
        $this->templateKey = $templateKey;
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Set transfer encode
        $this->withSwiftMessage(function(\Swift_Message $message){
            $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        });

        $template = Template::findByKey($this->templateKey);

        $subject = $template->getTitle();
        $subject = UserHandler::handle($subject, $this->user->getModel());

        $content = $template->getMessage();
        $content = UserHandler::handle($content, $this->user->getModel());

        if (count($this->data)) {
            $content = ReplaceHandler::handle($content, $this->data);
            $subject = ReplaceHandler::handle($subject, $this->data);
        }

        return $this
            ->subject($subject)
            ->view('emails.message-template', [
                'messageContent' => $content
            ]);
    }
}

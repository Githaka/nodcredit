<?php

namespace App\NodCredit\Message;

use App\Message as MessageModel;
use App\Events\SendMessage;
use App\NodCredit\Account\User;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MessageSender
{

    /**
     * @param string|Template $template
     * @param User $accountUser
     * @param array $data . [shortcode => value, ...]. For instance ['#AMOUNT#' => '100,000']
     * @param string $sender
     * @return boolean
     * @throws ModelNotFoundException
     */
    public static function send($template, User $accountUser, array $data = [], string $sender = 'system')
    {
        if (! $template) {
            throw new ModelNotFoundException();
        }

        if (! $template instanceof Template) {
            $template = Template::findByKey($template);
        }

        $content = $template->getMessage();
        $content = UserHandler::handle($content, $accountUser->getModel());

        if (count($data)) {
            $content = ReplaceHandler::handle($content, $data);
        }

        if (! $template->hasHtmlTags()) {
            $content = nl2br($content);
        }

        $message = MessageModel::create([
            'message' => $content,
            'message_type' => $template->getChannel(),
            'subject' => $template->getTitle(),
            'user_id' => $accountUser->getId(),
            'sender' => $sender
        ]);

        event(new SendMessage($message));

        return true;
    }

    public static function buildContent($template, User $accountUser, array $data = []): string
    {
        if (! $template) {
            throw new ModelNotFoundException();
        }

        if (! $template instanceof Template) {
            $template = Template::findByKey($template);
        }

        $content = $template->getMessage();
        $content = UserHandler::handle($content, $accountUser->getModel());

        if (count($data)) {
            $content = ReplaceHandler::handle($content, $data);
        }

        if (! $template->hasHtmlTags()) {
            $content = nl2br($content);
        }

        return $content;
    }




}
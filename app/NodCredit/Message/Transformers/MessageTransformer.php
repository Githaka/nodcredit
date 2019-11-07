<?php

namespace App\NodCredit\Message\Transformers;

use App\NodCredit\Message\Message;

class MessageTransformer
{

    public static function transform(Message $message, array $scopes = []): array
    {
        return [
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'message_type' => $message->getMessageType(),
            'status' => $message->getStatus(),
            'user_id' => $message->getUserId(),
            'created_at' => $message->getCreatedAt(),
        ];
    }

}
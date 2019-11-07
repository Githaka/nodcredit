<?php

namespace App\NodCredit\Message;

use App\Message as Model;
use App\NodCredit\Message\Transformers\MessageTransformer;


class Message
{

    /**
     * @var Model
     */
    private $model;

    public static function find(string $id): self
    {
        $model = Model::findOrFail($id);

        return new static($model);
    }

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    public function getStatus(): string
    {
        return $this->model->status;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->model->subject;
    }

    public function getMessageType(): string
    {
        return $this->model->message_type;
    }

    public function getMessage(): string
    {
        return $this->model->message;
    }

    public function getUserId(): string
    {
        return $this->model->user_id;
    }

    public function getCreatedAt()
    {
        return $this->model->created_at;
    }

    public function transform(): array
    {
        return MessageTransformer::transform($this);
    }

}
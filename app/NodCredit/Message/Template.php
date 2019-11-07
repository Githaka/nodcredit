<?php

namespace App\NodCredit\Message;

use App\MessageTemplate as Model;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class Template
{

    const CHANNEL_SMS = 'sms';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_BOTH = 'both';

    protected $handlers = [
        UserHandler::class
    ];

    /**
     * @var Model
     */
    private $model;

    public static function channels(): array
    {
        return [
            static::CHANNEL_SMS => [
                'name' => 'SMS',
                'value' => static::CHANNEL_SMS,
            ],
            static::CHANNEL_EMAIL => [
                'name' => 'Email',
                'value' => static::CHANNEL_EMAIL,

            ],
            static::CHANNEL_BOTH => [
                'name' => 'Both (sms, email)',
                'value' => static::CHANNEL_BOTH,
            ]
        ];
    }

    public static function rules(): array
    {
        return [
            'message' => 'required',
            'channel' => [
                'required',
                Rule::in(array_keys(static::channels()))
            ]
        ];
    }

    public static function find(string $id): self
    {
        $model = Model::findOrFail($id);

        return new static($model);
    }

    public static function findByKey(string $key): self
    {
        $model = Model::where('key', $key)->first();

        if (! $model) {
            throw new ModelNotFoundException("Template model with key '$key' not found.");
        }

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

    public function getKey(): string
    {
        return $this->model->key;
    }

    public function getName(): string
    {
        return ucfirst(str_replace('-',' ', $this->model->key));
    }

    public function getChannel(): string
    {
        return $this->model->channel;
    }

    public function getTitle()
    {
        return $this->model->title;
    }

    public function getMessage()
    {
        return $this->model->message;
    }

    public function update(array $data = [])
    {
        return $this->model->update($data);
    }

    public function isEmailChannel(): bool
    {
        return $this->getChannel() === static::CHANNEL_EMAIL;
    }

    public function isSmsChannel(): bool
    {
        return $this->getChannel() === static::CHANNEL_SMS;
    }

    public function isBothChannel(): bool
    {
        return $this->getChannel() === static::CHANNEL_BOTH;
    }

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $data)
    {
        return Validator::make($data, static::rules());
    }

    public function hasHtmlTags(): bool
    {
        return preg_match('/\<\w{1,6}\>/i', $this->getMessage());
    }

}
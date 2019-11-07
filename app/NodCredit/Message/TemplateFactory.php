<?php

namespace App\NodCredit\Message;

use App\MessageTemplate as Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class TemplateFactory
{

    public static function create(array $data = [])
    {
        $factory = new static();

        $validator = $factory->validate($data);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $model = Model::create($data);

        return new Template($model);
    }

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $data = [])
    {
        return Validator::make($data, Template::rules());
    }


}
<?php

namespace App\NodCredit\Message\Collections;

use App\Message as Model;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Message\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageCollection extends BaseCollection
{

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Message($model));
        }

        return $collection;
    }


    public function push(Message $message): self
    {
        $this->items->push($message);

        return $this;
    }

    public function transform(): array
    {
        $result = [];

        /** @var Message $message */
        foreach ($this->all() as $message) {
            $result[] = $message->transform();
        }

        return $result;
    }

}
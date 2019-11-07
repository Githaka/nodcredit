<?php

namespace App\NodCredit\Message\Collections;

use App\MessageTemplate as Model;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Message\Template;

class TemplateCollection extends BaseCollection
{

    public static function getAll()
    {
        $collection = new static();

        $models = Model::orderBy('key')->get();

        foreach ($models as $model) {
            $collection->push(new Template($model));
        }

        return $collection;
    }

    public function push(Template $template)
    {
        $this->items->push($template);

        return $this;
    }


}
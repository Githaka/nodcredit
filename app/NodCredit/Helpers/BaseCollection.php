<?php

namespace App\NodCredit\Helpers;

use Illuminate\Support\Collection;

class BaseCollection implements \Countable
{
    /**
     * @var Collection|array
     */
    protected $items;

    public function __construct($items = [])
    {
        if ($items instanceof Collection) {
            $this->items = $items;
        }
        else {
            $this->items = collect($items);
        }

    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function all(): array
    {
        return $this->items->all();
    }

    public function first()
    {
        return $this->items->first();
    }

}
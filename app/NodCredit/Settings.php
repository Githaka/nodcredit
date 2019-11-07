<?php

namespace App\NodCredit;

use App\Setting;
use Illuminate\Support\Collection;

class Settings
{


    /**
     * @var Collection
     */
    private $items;

    /**
     * Settings constructor.
     * @param Collection|null $items
     */
    public function __construct(Collection $items = null)
    {
        $this->items = $items;
    }

    /**
     * @param string $key
     * @param null $default
     * @return string|null
     */
    public function get(string $key, $default = null)
    {
        $index = $this->items->search(function(Setting $setting) use($key) {
            return $setting->k === $key;
        });

        if ($index === false) {
            return $default;
        }

        return $this->cast($this->items->get($index));
    }

    private function cast(Setting $setting)
    {
        if ($setting->type === 'array_comma') {
            return explode(',', $setting->v);
        }

        if ($setting->type === 'float') {
            return floatval($setting->v);
        }

        if ($setting->type === 'integer') {
            return intval($setting->v);
        }

        return $setting->v;
    }
}
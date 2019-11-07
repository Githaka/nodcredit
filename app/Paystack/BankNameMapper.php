<?php

namespace App\Paystack;


class BankNameMapper
{

    private $map = [
//        'ACCESS BANK (DIAMOND)' => 'DIAMOND BANK'
    ];

    public static function mapName(string $paystackName, string $default = null)
    {
        $self = new static();

        $paystackName = trim($paystackName);

        $value = array_get($self->getMap(), strtoupper($paystackName), $default);

        return strtoupper($value);
    }

    public function getMap(): array
    {
        return $this->map;
    }


}
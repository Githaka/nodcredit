<?php
namespace App\NodCredit\Account\Validators;

class NameMatching
{

    public static function validate(string $name, string $string): bool
    {
        $string = self::prepareName($string);
        $stringName = explode(' ', $string);

        $userName = self::prepareName($name);
        $userName = explode(' ', $userName);

        $diff = array_diff($stringName, $userName);

        if (count($diff) === 0 AND count($userName) === count($stringName)) {
            return true;
        }

        if (count($diff) === 1 AND (count($userName) > 2 AND count($stringName) > 1)) {
            return true;
        }

        if (count($diff) === 1 AND (count($userName) > 1 AND count($stringName) > 2)) {
            return true;
        }

        return false;
    }

    public static function prepareName(string $name): string
    {
        $name = str_replace([',', '.'], ' ', $name);
        $name = preg_replace('#\s{2,}#', ' ', $name);
        $name = trim(strtoupper($name));

        return $name;
    }
}
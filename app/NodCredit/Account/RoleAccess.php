<?php

namespace App\NodCredit\Account;

use App\User;

class RoleAccess
{
    private $accessMap = [
        User::ROLE_ADMIN => [
            User::ROLE_SUPPORT,
            User::ROLE_USER,
            User::ROLE_PARTNER,
        ],
    ];

    private $roles = [
        User::ROLE_USER,
        User::ROLE_PARTNER,
        User::ROLE_SUPPORT,
        User::ROLE_ADMIN,
    ];

    public static function hasAccess(string $role, string $to)
    {
        $checker = new static();

        if (! in_array($role, $checker->getRoles()) OR ! in_array($to, $checker->getRoles())) {
            throw new \Exception("Role [$role] or [$to] does not supported by system.");
        }

        if ($role === $to) {
            return true;
        }

        $accessMap = $checker->getAccessMap();

        if (array_get($accessMap, $role) AND in_array($to, $accessMap[$role])) {
            return true;
        }

        return false;
    }

    public function getAccessMap(): array
    {
        return $this->accessMap;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
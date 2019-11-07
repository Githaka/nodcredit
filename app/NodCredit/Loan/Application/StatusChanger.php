<?php

namespace App\NodCredit\Loan\Application;

use App\NodCredit\Loan\Application;

class StatusChanger
{
    private $fromToRelations = [

        Application::STATUS_NEW => [
            Application::STATUS_NEW,
            Application::STATUS_PROCESSING,
            Application::STATUS_APPROVAL,
            Application::STATUS_APPROVED,
            Application::STATUS_REJECTED,
            Application::STATUS_WAITING,
        ],
        Application::STATUS_WAITING => [
            Application::STATUS_WAITING,
            Application::STATUS_PROCESSING,
            Application::STATUS_APPROVAL,
            Application::STATUS_REJECTED,
        ],

        Application::STATUS_PROCESSING => [
            Application::STATUS_PROCESSING,
            Application::STATUS_NEW,
            Application::STATUS_APPROVAL,
            Application::STATUS_APPROVED,
            Application::STATUS_REJECTED,
            Application::STATUS_WAITING,
        ],

        Application::STATUS_APPROVAL => [
            Application::STATUS_APPROVAL,
            Application::STATUS_APPROVED,
            Application::STATUS_REJECTED,
        ],

        Application::STATUS_APPROVED => [
            Application::STATUS_COMPLETED,
        ]

    ];

    /**
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function canChange(string $from, string $to): bool
    {
        if (! $fromToArray = array_get($this->fromToRelations, $from)) {
            return false;
        }

        if (! in_array($to, $fromToArray)) {
            return false;
        }

        return true;
    }
}
<?php

namespace App\NodCredit\Loan\Transformers;

use App\NodCredit\Loan\Application;

class ApplicationTransformer
{

    public static function transform(Application $application, array $scopes = []): array
    {
        return [
            'id' => $application->getId(),
            'status' => $application->getStatus(),
            'amount_requested' => $application->getAmountRequested(),
            'amount_approved' => $application->getAmountApproved(),
            'tenor' => $application->getTenor(),
            'interest_rate' => $application->getInterestRate(),
            'loan_type_id' => $application->getLoanTypeId(),
            'created_at' => $application->getCreatedAt(),
            'user_id' => $application->getUserId(),
        ];
    }

}
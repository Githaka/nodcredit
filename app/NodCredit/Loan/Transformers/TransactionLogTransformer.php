<?php

namespace App\NodCredit\Loan\Transformers;

use App\NodCredit\Helpers\Money;
use App\TransactionLog;

class TransactionLogTransformer
{

    public static function transform(TransactionLog $log, array $scopes = []): array
    {
        return [
            'id' => $log->id,
            'amount' => Money::formatInNairaAsArray($log->amount),
            'created_at' => $log->created_at,
            'status' => $log->status,
            'pay_for' => $log->pay_for,
            'response_message' => $log->response_message,
        ];
    }

}
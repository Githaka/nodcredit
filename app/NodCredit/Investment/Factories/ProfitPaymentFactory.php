<?php

namespace App\NodCredit\Investment\Factories;

use App\NodCredit\Investment\Exceptions\ProfitPaymentFactoryException;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Models\ProfitPaymentModel;
use App\NodCredit\Investment\ProfitPayment;
use Illuminate\Support\Facades\Validator;

class ProfitPaymentFactory
{

    public static function create(array $data): ProfitPayment
    {
        $factory = new static();

        $validator = $factory->validate($data);

        if ($validator->fails()) {
            $exception = new ProfitPaymentFactoryException("Errors while creating a payment");
            $exception->setErrors($validator->errors());

            throw $exception;
        }

        $investment = Investment::find(array_get($data, 'investment_id'));

        $amount = array_get($data, 'amount');
        $taxAmount = $investment->getCalculation()->calculateWithholdingTaxAmount($amount);

        $model = new ProfitPaymentModel();
        $model->investment_id = $investment->getId();
        $model->amount = $amount;
        $model->status = array_get($data, 'status', 'scheduled');
        $model->period_days = (int) array_get($data, 'period_days');
        $model->period_start = array_get($data, 'period_start');
        $model->period_end = array_get($data, 'period_end');
        $model->scheduled_at = array_get($data, 'scheduled_at');
        $model->auto_payout = array_get($data, 'auto_payout', false);
        $model->withholding_tax_percent = $investment->getWithholdingTaxPercent();
        $model->withholding_tax_amount = $taxAmount;
        $model->payout_amount = floatval($amount - $taxAmount);

        if (! $model->save()) {
            throw new ProfitPaymentFactoryException('Saving error');
        }

        return new ProfitPayment($model);
    }

    public function validate(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'investment_id' => 'required|exists:investments,id',
            'amount' => 'required',
            'period_start' => 'required|date_format:Y-m-d H:i:s',
            'period_end' => 'required|date_format:Y-m-d H:i:s|after:period_start',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s|after:period_end',
        ]);
    }
}
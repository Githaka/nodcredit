<?php

namespace App\Http\Requests\API;

use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Payment;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class LoanRepaymentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (! $this->user()) {
            return false;
        }

        try {
            $application = Application::find($this->route('id'));
        }
        catch (\Exception $exception) {
            return false;
        }

        if ($application->getUserId() !== $this->user()->id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $applicationId = $this->route('id');

        return [
            'amount' => 'required|numeric|min:100',
            'card_id' => [
                'required',
                Rule::exists('user_cards', 'id')->where('user_id', $this->user()->id)
            ],
            'loan_payment_id' => [
                'required',
                Rule::exists('loan_payments', 'id')->where('loan_application_id', $applicationId)
            ]
        ];
    }

}

<?php

namespace App\Http\Requests\API;

use App\NodCredit\Loan\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanRequest extends BaseRequest
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
        return [];
    }
}

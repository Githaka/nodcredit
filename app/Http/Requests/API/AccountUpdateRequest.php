<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountUpdateRequest extends BaseRequest
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

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user();

        $rules = [];

        if ($this->get('password')) {
            $rules['current_password'] = [
                'required',
                function($attribute, $value, $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        return $fail(str_ireplace('_', ' ', $attribute) . ' is not valid');
                    }
                }
            ];
            $rules['password'] = 'min:8|confirmed';
            $rules['password_confirmation'] = 'required|min:8';
        }

        return $rules;
    }
}

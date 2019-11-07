<?php
/**
 * Created by PhpStorm.
 * User: vayrex
 * Date: 25.05.18
 * Time: 12:52
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;


class AuthValidateController extends Controller
{
    public function validateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users|max:150'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }
        return $this->successResponse('Email is valid');
    }

    public function validatePhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users|max:13'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }
        return $this->successResponse('Phone number is valid');
    }

    public function validateBvn(Request $request)
    {
        $bvn = $request->input('bvn');
        // TODO muse bvn validation service here

        return $this->successResponse('BVN is valid');
    }
}

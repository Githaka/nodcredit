<?php

namespace App\Http\Controllers;

use App\Role;
use App\WorkHistory;
use Illuminate\Http\Request;
use App\User;
use Hash;
use Validator;

class APIAuthController extends Controller
{
    public function login(Request $request)
    {

        $identity = $request->get('identity');
        $password = $request->get('password');

        $validator = Validator::make($request->all(), [
            'identity' => 'required|min:2|max:150',
            'password' => 'required|min:6|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }

        $user = User::where('email', $identity)->orWhere('phone', $identity)->first();
        if (!$user) {
            return $this->errorResponse('Account not found');
        }

        if (!Hash::check($password, $user->password)) {

            return $this->errorResponse('Unable to login, please check your login credentials.');
        }

        // $user->last_login = date('Y-m-d H:i:s');
        // $user->save();

        $user->createToken();
        return $this->successResponse('Logged in', $user->miniPayload());
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email|unique:users|max:150',
            'phone' => 'required|unique:users|max:13',
            'bvn' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }

        try {
            $data = $request->only(['name', 'phone', 'email', 'bvn']);
            $newPassword = str_random(16);
            $user = User::create(array_merge($data, ['password' => $newPassword]));
            $user->createToken();

            $clientRole = Role::find(['name'=>'client']);
            $user->attachRole($clientRole);
            //event(new UserAccountCreated($user));
            return $this->successResponse('account created', $user->miniPayload());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function me(Request $request)
    {
        return $this->successResponse('me', $request->user->miniPayload());
    }

    public function update(Request $request)
    {
        try {
            $request->user->bvn = $request->input('bvn');
            $request->user->save();
            return $this->successResponse('me', $request->user->miniPayload());
        } catch (\Exception $e) {
            return $this->errorResponse('Database error.');
        }
    }


    public function createWork(Request $request)
    {
        try {
            $request->user->works()->create($request->only(WorkHistory::$allowFields));
            return $this->successResponse('successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Database error');
        }
    }

    public function getWorks(Request $request)
    {

        return $this->successResponse('works', $request->user->works);
    }

    public function uploadAvatar(Request $request)
    {

        $user = $request->user;
        $oldAvatar = $user->avatar;
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|mimes:jpeg,bmp,png|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages());
        }

        if ($request->file('avatar')->isValid()) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
            $user->save();
            \Storage::disk('public')->delete($oldAvatar);
            return $this->successResponse('me', $request->user->miniPayload());
        } else {
            return $this->errorResponse('Avatar not valid. Please try again.');
        }
    }
}

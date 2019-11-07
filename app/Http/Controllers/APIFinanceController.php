<?php

namespace App\Http\Controllers;

use App\Bank;
use App\Setting;
use Illuminate\Http\Request;
use Validator;

class APIFinanceController extends Controller
{
    public function addBankDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'bank' => 'required|min:36|max:36',
            'account_name' => 'required',
            'account_number' => 'required|min:10|max:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }

        if (!Bank::find($request->input('bank'))) {
            return $this->errorResponse('Bank is not valid');
        }

        try {
            $request->user->bank = $request->input('bank');
            $request->user->account_number = $request->input('account_number');
            $request->user->account_name = $request->input('account_name');
            $request->user->save();
            return $this->successResponse('bank details updated', $request->user->miniPayload());
        } catch (\Exception $e) {
            return $this->errorResponse('Database error.');
        }
    }


    public function createTransferRecipient(Request $request)
    {
        $user = $request->user;

        if (!$user->bank) {
            return $this->errorResponse('Please update user bank details first.');
        }

        $payload = [
            'type' => 'nuban',
            'name' => $user->account_name,
            'description' => 'NodCredit Transfer',
            'account_number' => $user->account_number,
            'bank_code' => $user->bank->code,
            'currency' => 'NGN'
        ];
        $response = makePaystackPostRequest('https://api.paystack.co/transferrecipient', $payload);


        if ($response['status'] == 'error') {
            return $this->errorResponse($response['message']);
        }

        if ($response['data']->status) {
            $request->user->recipient_code = $response['data']->data->recipient_code;
            $request->user->save();
            return $this->successResponse('Created');
        }

        return $this->errorResponse('Error: ' . e($response['data']->message));
    }


    public function cardLinkInit(Request $request)
    {
        $email = $request->input('email');
        $investmentData = [];

        if ($email) {
            $validator = Validator::make($request->all(), [
                'email' => 'email|unique:users|max:150'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }
        } else {
            $email = $request->user->email;
        }

        if($request->input('action') == 'invest')
        {

            $reason = 'investment';
            $amount = doubleval(str_replace(',','',$request->input('amount')));
            $investmentType = $request->input('investmentType');

            $maxInvest = Setting::v('max_investment');
            $minInvest = Setting::v('min_investment');
            $investmentTypes = json_decode(Setting::v('investmentConfig'));

            $selectedInvestmentType = null;
            if($amount > $maxInvest)
            {
                return $this->errorResponse('The maximum amount you can invest is ' . number_format($maxInvest));
            }

            if($amount < $minInvest)
            {
                return $this->errorResponse('The minimum amount you can invest is ' . number_format($minInvest));
            }

            foreach($investmentTypes as $_investmentType)
            {
                if($_investmentType->value === $investmentType)
                {
                    $selectedInvestmentType = $_investmentType;
                    break;
                }
            }

            if(!$selectedInvestmentType)
            {
                return $this->errorResponse('Please select a valid investment tenor');
            }

            $investmentData = [
                'is_investment' => true,
                'investment_tenor' => $selectedInvestmentType->value
            ];

        }
        else
        {
            $amount = 20;
            $reason = 'card-link';
        }

        $payload = [
            'amount' => $amount * 100, // this amount is in kobo 100kobo = 1 Naira
            'email' => $email,
            'reusable' => true
        ];

        if ($request->input('callback_url')) {
            $payload['callback_url'] = $request->input('callback_url');
        }

        $response = makePaystackPostRequest('https://api.paystack.co/transaction/initialize', $payload);

        if ($response['status'] !== 'ok') {
            return $this->errorResponse($response['message']);
        }

        if ($response['data']->status) {

            $insertData = [
                'amount' => $amount,
                'reason' => $reason,
                'payment_reference' => $response['data']->data->reference
            ];

            $insertData = array_merge($insertData, $investmentData);
            $request->user->payments()->create($insertData);
            return $this->successResponse('success', (array)$response['data']->data);
        }

        return $this->errorResponse($response['data']->message);

    }


    public function cardLinkVerify(Request $request, $reference)
    {

        $payment = $request->user->payments()
            ->where('payment_reference', $reference)
            ->first();
        if (!$payment) return $this->errorResponse('Payment details not found');

        $response = makePaystackGetRequest("https://api.paystack.co/transaction/verify/" . rawurlencode($reference));

        if ($response['status'] !== 'ok') {
            return $this->errorResponse($response['message']);
        }

        if ($response['data']->status) {
            return $this->successResponse('success', $response['data']->message);
        }

        return $this->errorResponse($response['data']->message);
    }

}

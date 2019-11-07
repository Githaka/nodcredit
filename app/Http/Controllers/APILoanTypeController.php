<?php

namespace App\Http\Controllers;

use App\LoanType;
use App\Setting;
use Illuminate\Http\Request;



class APILoanTypeController extends Controller
{
    public function index(Request $request)
    {

        $output = [
            'loanTypes' =>  LoanType::get(),
            'loanSetting' => ['min' => doubleval(Setting::v('loan_min')), 'max' =>doubleval(Setting::v('loan_max')) ],
            ];

        if($request->session()->has('loanInfo'))
        {
            $output['loanInfo'] = $request->session()->get('loanInfo');
        }
        return $this->successResponse('loan info',$output);
    }
}

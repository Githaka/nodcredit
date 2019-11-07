<?php

namespace App\Http\Controllers;

use App\LoanRange;
use Illuminate\Http\Request;

class UiAdminLoanRangeController extends AdminController
{

    public function index()
    {


        return view('admin.loan-range')
                ->with('settings', LoanRange::all())
                ->with('title', 'Loan range');
    }

    public function store(Request $request)
    {
       $loanRange = LoanRange::findOrFail($request->input('id'));
       $loanRange->min  = doubleval(str_ireplace(',', '', $request->input('min')));
       $loanRange->max  = doubleval(str_ireplace(',', '', $request->input('max')));
       $loanRange->min_month  = intval($request->input('min_month'));
       $loanRange->max_month  = intval($request->input('max_month'));
       $loanRange->min_score  = doubleval(str_ireplace(',', '', $request->input('min_score')));
       $loanRange->max_score  = doubleval(str_ireplace(',', '', $request->input('max_score')));
       $loanRange->save();

        return back()->with('success', 'Saved');
    }
}

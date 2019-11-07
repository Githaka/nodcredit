<?php

namespace App\Http\Controllers;

use App\LoanApplication;
use Illuminate\Http\Request;

class APIAdminLoanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page') ?: 30;

        $filter = (new LoanApplication)->newQuery();
        $filter->with('owner', 'documents');


        return $this->successResponse('loans', $filter->paginate($perPage));
    }
}

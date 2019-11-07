<?php

namespace App\Http\Controllers;

use App\LoanApplication;
use App\LoanDocument;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;

class APILoanController extends Controller
{

    public function create(Request $request)
    {
        $messages = [
            'amount_requested.min' => 'Our minimum loan is 10,000',
            'amount_requested.max' => 'Our Maximum loan is 7,000,000'
        ];

        $validator = Validator::make($request->all(), [
            'loan_type_id'=>'required|exists:loan_types,id',
            'amount_requested'=>'required|numeric|min:10000|max:7000000'
        ], $messages);

        $data = $validator->validate();
        if (!$data) {
            return $this->errorResponse($validator->messages());
        }

        $data['amount_requested'] = floatval($data['amount_requested']);
        $data['amount_approved'] = 0;

        $loan = $request->user->applications()->create($data);
        return $this->successResponse('Loan application created', $loan);
    }

    public function getLoans(Request $request)
    {
        $filter  = (new LoanApplication)->newQuery()->with('loanType', 'documents');
        $perpage = $request->input('per_page') ?: 50;

        $filter->where('user_id', $request->user->id);

        return $this->successResponse('loans', $filter->paginate($perpage));
    }

    public function uploadLoanDocument(Request $request, $id)
    {

        $loan = $request->user->applications()->find($id);

        if(!$loan) return $this->errorResponse('Loan application not valid.');

        $validator = Validator::make($request->all(), [

            'description' => 'max:1000',
            'name' => 'required|min:6',
            'document' =>  'required|mimes:pdf,csv|max:5000',

        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages());
        }

        $document = LoanDocument::create([
                        'loan_application_id' => $loan->id,
                        'path' => '',
                        'document_type' => $request->input('name'),
                        'description' => $request->input('description'),

                ]);

        if($request->file('document')->isValid())
        {
            $document->path  = $request->file('document')->store($loan->id, 'documents');
            $document->document_extension = $request->file('document')->extension();
            $document->save();

        }
        else
        {
            return $this->errorResponse('Document not valid. Please try again.');
        }

        return $this->successResponse('Uploaded');
    }

    public function getLoanDocument(Request $request, $id)
    {
        $loan = $request->user->applications()->find($id);

        if(!$loan) return $this->errorResponse('Loan application not valid.');

        return $this->successResponse($loan->documents);
    }
}

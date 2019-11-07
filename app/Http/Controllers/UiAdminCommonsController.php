<?php

namespace App\Http\Controllers;

use App\LoanDocumentType;
use Illuminate\Http\Request;

class UiAdminCommonsController extends AdminController
{
    public function loanDocumentType()
    {

        return view('admin.commons-loan-doc-type')
                    ->with('documentTypes', LoanDocumentType::get())
                    ->with('title', 'Loan document type');
    }

    public function loanDocumentTypeStore(Request $request)
    {

        $docType = $request->input('id') ? LoanDocumentType::findOrFail($request->input('id')) : new LoanDocumentType;

        $docType->name = $request->input('name');
        $docType->is_required = intval($request->input('is_required'));
        $docType->file_type = trim($request->input('file_type'));
        $docType->save();
        return redirect()->route('admin.commons.loan.doc-type')
                            ->with('success', 'Updated');



    }
}

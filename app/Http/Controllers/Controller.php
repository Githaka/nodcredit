<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Response;
use Illuminate\Support\MessageBag;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function successResponse($message='', $data=null)
    {
        return Response::json([
            'data' => $data,
            'message' => $message,
            'status' => 'success'
        ], 200);
    }


    protected function errorResponseold($message, $data=null)
    {
        return Response::json([
            'data' => $data,
            'message' => $message,
            'status' => 'error'
        ], 200);
    }


    protected function validationErrorsToString($errArray) {
        $valArr = array();
        foreach ($errArray->toArray() as $key => $value) {
            $errStr = $key.' '.$value[0];
            array_push($valArr, $errStr);
        }
        if(!empty($valArr)){
            $errStrFinal = implode(',', $valArr);
        }
        return $errStrFinal;
    }

    protected function extractErrorMessageFromArray($errors)
    {
        $err = [];

        foreach ($errors as $key => $value) {

            $err[]  = is_array($value) ? implode("\n", $value) : $value;
        }
        return implode("\n", $err);
    }

    protected function errorResponse($errors, $data=null) {


        if($errors instanceof MessageBag)
        {
            $errors = $this->extractErrorMessageFromArray($errors->getMessages());

        } else if(is_array($errors))
        {
            $errors = $this->extractErrorMessageFromArray($errors);
        }

        return Response::json([
            'data' => $data,
            'message' => $errors,
            'status' => 'error'
        ], 422);
    }
}


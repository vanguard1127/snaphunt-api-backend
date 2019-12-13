<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public static function sendCustomResponse($message = '', $status = 400)
    {
        return response(["message" => $message], $status);
    }

    public function sendData($data = null, $status = 200)
    {
        return response(["data" => $data], $status);
    }

    public function errorArrayWithKey($key = "exception", $msg = "Something went wrong, Please try again", $status = 400)
    {
        return response(["errors" => [$key => $msg]], $status);
    }

    public function errorArray($msg = "Something went wrong, Please try again", $status = 400)
    {
        return response(["errors" => ["ex" => $msg]], $status);
    }

    public function validateData($data, $rules){
        $validator = Validator::make($data,$rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function validationError($ex, $status = 400){
        return response(["errors" =>$ex->errors()], $status);
    }

}

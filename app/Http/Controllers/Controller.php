<?php

namespace App\Http\Controllers;

use App\Traits\HttpTrait;
use App\Traits\MediaTrait;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use HttpTrait, NotificationTrait;
    
    public static function sendCustomResponse($message = '', $status = 400)
    {
        return response(["message" => $message], $status);
    }

    public function sendData($data = null, $status = 200)
    {
        return response( $data, $status);
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

    public function fourDigitCode($x){
        return substr(str_shuffle("0123456789"), 0, $x);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return $this->errorArray('user not found');
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return $this->errorArray('token expired');
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->errorArray('invalid token');
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->errorArray('token absent');
        }
        // the token is valid and we have found the user via the sub claim
        return $user;
    }

    public function sendPushNotification($toUser, $title, $message, $data){
        $URL = 'https://exp.host/--/api/v2/push/send';
        $headers = [
            "Accept" => 'application/json',
            'Accept-encoding' => 'gzip, deflate',
            'Content-Type' => 'application/json',
        ];
        $message = [
            "to" => $toUser["expo_token"],
            "sound" => 'default',
            "title" => $title,
            "body" => $message,
            "data" =>  $data,
            "_displayInForeground" =>  true,
        ];
        $this->postRequest($URL, $message, $headers);
    }

}

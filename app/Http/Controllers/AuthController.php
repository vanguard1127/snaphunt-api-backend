<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Get register user in db with unverified state
     */
    public function register(Request $request)
    {
        try {
            $data = $request->all();
            $this->validateData($data, User::$signUpRules);
            $data['password'] = bcrypt($data['password']);
            $code = uniqid();
            $data['id_code'] = $code;
            $user = User::create($data);
            if($user){
                return $this->sendData(["username" => $user->name, "code" => $code]);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

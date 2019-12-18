<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\AuthTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use AuthTrait;
    /**
     * Get register user in db with unverified state
     */
    public function register(Request $request)
    {
        try {
            $data = $request->all();
            $this->validateData($data, User::$signUpRules);
            $data['password'] = Hash::make($data['password']);
            $code = $this->fourDigitCode(4);
            $data['id_code'] = $code;
            $data["dob"] = new Carbon($data["year"].'-'.$data["month"].'-'.$data["day"]);
            $user = User::create($data);
            if($user){
                // send him verification email
                $this->sendVerificationEmail($data["email"],$data["username"], $code); 
                return $this->sendCustomResponse("Verification code sent", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function verifyCode(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["id_code" => "required", "email" => "required"]);
            if(User::where("email", $data["email"])->where("id_code", $data["id_code"])->first()){
                User::where("email", $data["email"])->where("id_code", $data["id_code"])->update(["status" => 1]);
                return $this->sendCustomResponse("Account verified", 200);
            }
            return $this->errorArray("Invalid verification code", 400);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
           $data = $request->all();
           $name = $data["email"];
           $field = filter_var($name, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
           unset($data["name"]);
           $data[$field] = $name;
           $token = JWTAuth::attempt($data);
           if (!$token) {
               return $this->errorArray('Invalid username/password');
           }
           $user = User::where($field, $data[$field])->first();
           if ($user->status == 0) {
               // user is pending redirect to second step
               return $this->sendCustomResponse("Please verify account", 302);
           }
           return $this->sendData(["access_token" => $token]);

        } catch(ValidationException $ex){
            return $this->validationError($ex);
        } catch (JWTException $e) {
            return $this->errorArray('Token could not be generated');
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        } 
    }

    public function facebookLogin(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["accessToken" => "required"]);
            $endpoint = "/me?fields=email,first_name,last_name,id,name,birthday";
            $resp = $this->fbGetRequest($data["accessToken"], $endpoint);
            if($token = $this->processFbData($resp, $data["accessToken"])){
                return $this->sendData(["access_token" => $token], 200);
            }
        //    return $this->errorArray("Invalid verification code", 400);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function authMe(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            return $this->sendData(["id" => $user["uuid"]], 200);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->sendData("logout successfully", 200);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

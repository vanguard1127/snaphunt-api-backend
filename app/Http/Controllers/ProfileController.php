<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\MediaTrait;
use App\Traits\ProfileTrait;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ProfileTrait, MediaTrait;
    
    public function getProfile(Request $request){
        try {
            $data = $request->all();
            if(isset($data['id'])){
                $userId = $data['id'];
            }else{
                $userId = $this->getAuthenticatedUser()['uuid'];
            }
            $user = User::where("uuid", $userId)->with("challenges")->first();
            if($user){
                $response = [
                    "uuid" => $user['uuid'],
                    "username" => $user["username"],
                    "full_name" => $user['first_name']." ".$user["last_name"],
                    "challenges_count" => $user->challenges->count(),
                    "challenges" => $this->myChallenges($user->challenges)
                ];
                return $this->sendData($response);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}
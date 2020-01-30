<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;

class ProfileController extends Controller
{    
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
                    "followers_count" => Friend::totalFollowers($user["uuid"]),
                    "followings_count" => Friend::totalFollowings($user["uuid"]),
                    "points" => $user["points"],
                    "challenges" => $this->prepareChallenges($user->challenges)
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

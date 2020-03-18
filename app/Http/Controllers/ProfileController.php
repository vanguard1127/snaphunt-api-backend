<?php

namespace App\Http\Controllers;

use App\Helpers\MediaHelper;
use App\Models\Friend;
use App\Models\User;
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
                    "avatar" =>  MediaHelper::getFullURL($user["avatar"]),
                    "full_name" => $user['first_name']." ".$user["last_name"],
                    "challenges_count" => $user->challenges->count(),
                    "followers_count" => Friend::totalFollowers($user["uuid"]),
                    "followings_count" => Friend::totalFollowings($user["uuid"]),
                    "points" => $user["points"],
                    "challenges" => ChallengeHelper::prepareChallenges($user->challenges, $user["uuid"]),
                    "unread_notifications" => $this->notifications($user)
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

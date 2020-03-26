<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{    
    public function getProfile(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, [
                "limit" => "required",
                "offset" => "required"
            ]);
            if(isset($data['id'])){
                $userId = $data['id'];
            }else{
                $userId = $this->getAuthenticatedUser()['uuid'];
            }

            if($data["offset"] > 0){
                $challenges = ChallengeModel::where("owner_id", $userId)->limit($data["limit"])->offset($data["offset"])->orderBy("created_at", "desc")->get();
                if($challenges){
                    $response = [
                        "challenges" => ChallengeHelper::prepareChallenges($challenges, $userId),
                    ];
                    return $this->sendData($response);
                }
            }   
            else{
                $user = User::where("uuid", $userId)->with(["challenges" => function($sql) use($data){
                    $sql->limit($data["limit"])->offset(0)->orderBy("created_at", "desc");
                }])->first();

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
            }

            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

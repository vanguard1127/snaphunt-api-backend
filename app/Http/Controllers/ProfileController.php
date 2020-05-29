<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;
use App\Models\Friend;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Http\Request;

class ProfileController extends Controller
{    
    public function getProfile(Request $request){
        try {
            $data = $request->all();
            $drafts = 0;
            $authUser = $this->getAuthenticatedUser();
            $requestStatus = null;

            $this->validateData($data, [
                "limit" => "required",
                "offset" => "required",
                "type" => "required"
            ]);
            $snapoffed = $data["type"] == 1 ? true : false;
            if(isset($data['id']) && $data["id"] != "null"){
                $userId = $data['id'];
                $requestStatus = Friend::getFollowStatus($userId, $authUser["uuid"]);
            }else{
                $userId = $authUser['uuid'];
            }

            if($authUser["uuid"] == $userId && !$snapoffed){
                if($draftCount = ChallengeModel::selectRaw("COUNT(*) as drafts")->where("is_draft", true)->where("owner_id", $userId)->first()){
                    $drafts = $draftCount["drafts"];
                }
            }

            if($data["offset"] > 0){
                $challenges = ChallengeModel::where("owner_id", $userId);
                if($snapoffed){
                    $challenges = $challenges->where("original_post", "!=", null);
                }else{
                    $challenges = $challenges->where("original_post", null);
                }
                $challenges = $challenges->limit($data["limit"])->offset($data["offset"])->orderBy("created_at", "desc")->get();
                if($challenges){
                        $response = ChallengeHelper::prepareChallenges($challenges, $userId);
                        return $this->sendData($response);
                }
            }   
            else{
                $user = User::where("uuid", $userId)->with(["challenges" => function($sql) use($data, $snapoffed){
                    if($snapoffed){
                        $sql = $sql->where("original_post", "!=", null);
                    }else{
                        $sql = $sql->where("original_post", null);
                    }
                    $sql->limit($data["limit"])->offset(0)->orderBy("created_at", "desc");
                }])->first();

                if($user){
                    $response = [
                        "uuid" => $user['uuid'],
                        "is_private" => UserSettings::isPrivate($user["uuid"]),
                        // "username" => $user["username"],
                        // "avatar" =>  MediaHelper::getFullURL($user["avatar"]),
                        // "full_name" => $user['first_name']." ".$user["last_name"],
                        "challenges_count" => $user->challenges->count(),
                        "followers_count" => Friend::totalFollowers($user["uuid"]),
                        "followings_count" => Friend::totalFollowings($user["uuid"]),
                        "points" => $user["points"],
                        "challenges" => ChallengeHelper::prepareChallenges($user->challenges, $user["uuid"]),
                        "unread_notifications" => $this->notifications($user),
                        "drafts" => $drafts,
                        "request_status" => $requestStatus
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

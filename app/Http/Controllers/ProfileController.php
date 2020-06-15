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
           // $snapoffed = $data["type"] == 1 ? true : false;
            $notIn = [];
            if(isset($data['id']) && $data["id"] != "null"){
                $userId = $data['id'];
                $requestStatus = Friend::getFollowStatus($userId, $authUser["uuid"]);
            }else{
                $userId = $authUser['uuid'];
            }

            if($authUser["uuid"] == $userId && $data["type"] == 0){
                if($draftCount = ChallengeModel::selectRaw("COUNT(*) as drafts")->where("is_draft", true)->where("status", 1)->where("owner_id", $userId)->first()){
                    $drafts = $draftCount["drafts"];
                }
            }

            if($data["type"] == 0){
                $notIn = ChallengeModel::select("uuid")->where("owner_id", $userId)->where("status", 1)->whereHas("org_post", function($sql){
                    $sql->where("type", "user");
                })->get();
            }

            if(isset($data["second"]) && $data["second"] == "true"){
                if($data["type"] == 0 || $data["type"] == 1){
                    $challenges = ChallengeModel::where("owner_id", $userId)->where("status", 1);
                    if($data["type"] == 1){
                        $challenges = $challenges->where("original_post", "!=", null)->where("category", "!=", 17);
                    }else{
                        $challenges = $challenges->whereNotIn("uuid",$notIn);
                    }
                    $challenges = $challenges->limit($data["limit"])->offset($data["offset"])->orderBy("created_at", "desc")->get();
                    if($challenges){
                            $response = ChallengeHelper::prepareChallenges($challenges, $userId);
                            return $this->sendData($response);
                    }
                }else if ($data["type"] == 2){
                    return $this->sendData(ChallengeHelper::preparePinnedPost($userId));
                }
            }   
            else{
                $user = User::where("uuid", $userId)->with(["challenges" => function($sql) use($data, $notIn){
                    if($data["type"] == 1){
                        $sql->where("original_post", "!=", null)->where("category", "!=", 17);
                    }else{
                        $sql->whereNotIn("uuid",$notIn);
                    }
                    $sql->where("status", 1)->limit($data["limit"])->offset(0)->orderBy("created_at", "desc");
                }])->first();

                if($user){
                    $response = [
                        "uuid" => $user['uuid'],
                        "bio" => $user['bio'],
                        "website" => $user['website'],
                        "is_private" => UserSettings::isPrivate($user["uuid"]),
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

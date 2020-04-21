<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\MediaHelper;
use App\Models\Friend;
use App\Models\User;
use App\Models\UserSettings;
use App\Notifications\FollowNotification;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function makeFriends(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $msg = "";
            $this->validateData($data, ["following_id" => "required"]);
            $followingUser = User::where("uuid", $data["following_id"])->first();
            if(Friend::where("following_id", $data["following_id"])->where("follower_id", $user["uuid"])->first()){
                // unfollow them
                Friend::where("following_id", $data["following_id"])->where("follower_id", $user["uuid"])->delete();
                $msg = "Successfully unfollowed ".$followingUser["username"];
            }else{
                // follow each other
                if($settings = UserSettings::where("user_id", $data["following_id"])->first()){
                    if($settings->private_account){
                        Friend::makeFriends($data["following_id"], $user["uuid"]);
                        $msg = "Follow request sent to ".$followingUser["username"];
                        $followingMsg = $user["username"]. " sent you follow request";
                        $this->sendPushNotification($followingUser, $user["username"], $followingMsg, ["route" => "ActivityScreen", "data" => []]);
                        // $followingUser->notify(new FollowNotification($user->uuid, $followingMsg));
                    }else{
                        Friend::makeFriends($data["following_id"], $user["uuid"], "active");
                        $msg = "Started following ".$followingUser["username"];
                        $followingMsg = $user["username"]. " started following you";
                        $this->sendPushNotification($followingUser, $user["username"], $followingMsg, ["route" => "Home", "data" => []]);
                    }
                }else{
                    Friend::makeFriends($data["following_id"], $user["uuid"], "active");
                    $msg = "Started following ".$followingUser["username"];
                    $followingMsg = $user["username"]. " started following you";
                    $this->sendPushNotification($followingUser, $user["username"], $followingMsg, ["route" => "Home", "data" => []]);
                }
            }
            return $this->sendCustomResponse($msg, 200);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function acceptRequest(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["follower_id" => "required"]);
            $follower = User::where("uuid", $data["follower_id"])->first();
            if($friend = Friend::where("following_id", $user["uuid"])->where("follower_id", $data["follower_id"])->where("status", "pending")->first()){
                $friend->status = "active";
                $friend->save();
            }
            return $this->sendCustomResponse("You are now friend with ".$follower["username"], 200);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function cancelRequest(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["follower_id" => "required"]);
            if($friend = Friend::where("following_id", $user["uuid"])->where("follower_id", $data["follower_id"])->where("status", "pending")->first()){
                $friend->delete();
            }
            return $this->sendCustomResponse("Request has been rejected", 200);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function getFreinds(Request $request){
        try {
            $data = $request->all();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $resp = [];
            $user = $this->getAuthenticatedUser();
            $friends = Friend::myFriends($user["uuid"], $limit, $offset);
            foreach($friends as $friend){
                $friendId = $friend["follower_id"] == $user["uuid"] ? $friend["following_id"] : $friend["follower_id"];
                $friendObj = User::where("uuid", $friendId)->first();
                $resp[$friendId] = [
                    "uuid" => $friendId,
                    "username" => $friendObj["username"],
                    "avatar" => MediaHelper::getFullURL($friendObj["avatar"]),
                    "first_name" => $friendObj["first_name"],
                    "last_name" => $friendObj["last_name"]
                ];
            }
            return $this->sendData(array_values($resp));
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function getFollowers(Request $request){
        try {
            $data = $request->all();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $resp = [];
            $user = $this->getAuthenticatedUser();
            $friends = Friend::getFollowers($user["uuid"], $limit, $offset);
            foreach($friends as $friend){
                $resp[] = CommonHelper::prepareFriendObj($friend["follower"], $user);
            }
            return $this->sendData(array_values($resp));
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function getFollowings(Request $request){
        try {
            $data = $request->all();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $resp = [];
            $user = $this->getAuthenticatedUser();
            $friends = Friend::getFollowings($user["uuid"], $limit, $offset);
            foreach($friends as $friend){
                $resp[] = CommonHelper::prepareFriendObj($friend["following"], $user);
            }
            return $this->sendData(array_values($resp));
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

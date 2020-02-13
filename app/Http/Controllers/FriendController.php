<?php

namespace App\Http\Controllers;

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
                        $followingUser->notify(new FollowNotification($user->uuid, $followingMsg));
                    }else{
                        Friend::makeFriends($data["following_id"], $user["uuid"], "active");
                        $msg = "Started following ".$followingUser["username"];
                    }
                }else{
                    Friend::makeFriends($data["following_id"], $user["uuid"], "active");
                    $msg = "Started following ".$followingUser["username"];
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
}
<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeHelper;
use App\Helpers\CommonHelper;
use App\Models\ChallengeModel;
use App\Models\Claps;
use App\Models\User;
use App\Notifications\ClapNotification;
use Illuminate\Http\Request;

class ClapsController extends Controller
{
    public function addClap(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Claps::$addClapRules);
            $user = $this->getAuthenticatedUser();
            $post = ChallengeModel::where("uuid", $data["post_id"])->with("owner")->first();
            if(Claps::where("post_id", $data["post_id"])->where("user_id", $user["uuid"])->first()){
                // remove clap
                Claps::where("post_id", $data["post_id"])->where("user_id", $user["uuid"])->delete();
                if($post["owner_id"] != $user["uuid"]){
                    User::updatePoints($user["uuid"], config("general.points.clap"), "remove");
                }
            }else{
                Claps::addClap($data["post_id"], $user["uuid"]);
                $title  = $user["username"]. " clapped on your post!";
                $message = "";
                $navData = [
                    "route" => "SinglePost",
                    "data" => [ "data" => ChallengeHelper::singleChallenge($post["owner"], $post, $user["uuid"], false)]
                ];
                $this->sendPushNotification($post["owner"], $title, $message, $navData);
                $post->owner->notify(new ClapNotification($user["uuid"], $title, $message, $navData));
                if($post["owner_id"] != $user["uuid"]){
                    User::updatePoints($user["uuid"], config("general.points.clap"));
                }
            }
            return $this->sendCustomResponse("Clap Added", 200);
            //return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function clappedUsers(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, [
                "postId" => "required"
            ]);
            $resp = [];
            $myUser = $this->getAuthenticatedUser();
            $clappedUsers = Claps::where("post_id", $data["postId"])->with("user")->limit($data["limit"])->offset($data["offset"])->get();
            foreach($clappedUsers as $user){
                $resp[] = CommonHelper::prepareFriendObj($user["user"], $myUser);
            }   
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

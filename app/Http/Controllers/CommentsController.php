<?php

namespace App\Http\Controllers;

use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;
use App\Models\Comments;
use App\Notifications\CommentNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    public function addComment(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Comments::$addCommentRules);
            $user = $this->getAuthenticatedUser();
            if($newComment = Comments::addComment($data, $user["uuid"])){
                $post = ChallengeModel::where("uuid", $newComment["post_id"])->with("owner")->first();
                $title  = $user["username"]. " commented on your post!";
                $message = $newComment["comments"];
                $navData = [
                    "route" => "DetailWithComments",
                    "data" => [ "uuid" => $post["uuid"] ]
                ];
                $this->sendPushNotification($post["owner"], $title, $message, $navData);
                $post->owner->notify(new CommentNotification($user["uuid"], $title, $message, $navData));
                return $this->sendCustomResponse("Comment Added", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function getComments(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Comments::$getCommentsRules);
            $resp = [];
            $comments = Comments::where("post_id", $data["post_id"])->with("user")->orderBy("created_at", "ASC")->get();
            foreach($comments as $comment){
                $resp[] = [
                    "avatar" => MediaHelper::getFullURL($comment["user"]["avatar"]),
                    "username" => $comment["user"]["username"],
                    "comment" => $comment["comments"],
                    "ts" => Carbon::parse($comment["created_at"])
                ]; 
            }
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Traits\MediaTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    use MediaTrait;

    public function addComment(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Comments::$addCommentRules);
            $user = $this->getAuthenticatedUser();
            if(Comments::addComment($data, $user["uuid"])){
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
            $comments = Comments::where("post_id", $data["post_id"])->with("user")->orderBy("created_at", "DESC")->get();
            foreach($comments as $comment){
                $resp[] = [
                    "avatar" => $this->getFullURL($comment["user"]["avatar"]),
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

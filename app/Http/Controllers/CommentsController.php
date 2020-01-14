<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    public function addComment(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Comments::$addCommentRules);
            $user = $this->getAuthenticatedUser();
            if(Comments::addComment($data["post_id"], $user["uuid"], $data["comment"])){
                return $this->sendCustomResponse("Comment Added", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

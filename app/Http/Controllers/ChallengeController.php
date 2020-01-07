<?php

namespace App\Http\Controllers;

use App\Models\ChallengeModel;
use App\Traits\ChallengeTrait;
use App\Traits\MediaTrait;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    use ChallengeTrait, MediaTrait;

    public function saveChallenge(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ChallengeModel::$createChallengeRules);
            if($request->file("media")->isValid()){
                if($mediaNames = $this->uploadToS3($data["media"], $data["post_type"])){
                    $data["media"] = $mediaNames["media_name"];
                    $data["thumb"] = $mediaNames["thumb_name"];
                    ChallengeModel::createChallenge($data, $user["uuid"]);
                    return $this->sendCustomResponse("Challenge created", 200);
                }
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }
}

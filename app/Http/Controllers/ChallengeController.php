<?php

namespace App\Http\Controllers;

use App\Models\ChallengeModel;
use App\Traits\ChallengeTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    use ChallengeTrait, CommonTrait;

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
            }else{
                return $this->errorArray($request->file("media")->getErrorMessage());
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function getSavedChallenges(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 10;

            $challneges = ChallengeModel::where("owner_id", $user["uuid"])->where("is_draft", true)->limit($limit)->offset($offset)->get();
            $savedChallenges = $this->prepareChallenges($challneges);
            return $this->sendData($savedChallenges);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }
}

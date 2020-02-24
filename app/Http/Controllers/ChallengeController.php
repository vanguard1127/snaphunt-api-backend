<?php

namespace App\Http\Controllers;

use App\Models\ChallengeModel;
use App\Traits\ChallengeTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{
    use ChallengeTrait, CommonTrait;

    public function saveChallenge(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ChallengeModel::$createChallengeRules);
            if($data["already_saved"] == "true"){
                $update = ChallengeModel::where("uuid", $data["uuid"])->update([
                    "description" => $data["description"],
                    "category" => is_string($data["category"]) ? null : $data["category"],
                    "privacy" => $data["privacy"],
                    "is_draft" => false
                ]);
                if($update){
                    return $this->sendCustomResponse("Challenge created", 200);
                }
            }else{
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

    public function deleteDraft(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["uuid" => "required"]);
            $user = $this->getAuthenticatedUser();
            if($challenge = ChallengeModel::where("uuid", $data["uuid"])->where("is_draft", true)->where("owner_id", $user["uuid"])->first()){
                Storage::disk('s3')->delete([$challenge["media"], $challenge["thumb"]]);
                $challenge->delete();
                return $this->sendCustomResponse("Draft deleted", 200);
            }
            return $this->sendCustomResponse("You are not authorised to do this.");
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function uploadS3Api(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["media" => "required", "post_type" => "required" ]);
            $resp = [];
            // $user = $this->getAuthenticatedUser();
            if($mediaNames = $this->uploadToS3($data["media"], $data["post_type"], $data["width"], $data["height"])){
                $resp["media"] = $mediaNames["media_name"];
                $resp["thumb"] = $mediaNames["thumb_name"];
                return $this->sendData($resp);
            }
            return $this->sendCustomResponse("You are not authorised to do this.");
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }
}

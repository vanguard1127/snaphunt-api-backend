<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;
use App\Models\PinPost;
use App\Traits\ChallengeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{

    public function saveChallenge(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $width = isset($data["width"]) ? $data["width"] : 250;
            $height = isset($data["height"]) ? $data["height"] : 250;
            $this->validateData($data, ChallengeModel::$createChallengeRules);

            if($user["paid"] == false){
                return $this->sendCustomResponse("You have created 10 free challenges, please subscribe to our monthly package.");
            }

            if(isset($data["already_saved"]) && $data["already_saved"] == "true"){

                $update = ChallengeModel::where("uuid", $data["uuid"])->update([
                    "description" => $data["description"],
                    "category" => $data["category"],
                    "privacy" => $data["privacy"],
                    "is_draft" => false
                ]);
                if($update){
                    return $this->sendData(["paid" => ChallengeModel::freeStatus($user)]);
                }
            }else{
                if($request->file("media")->isValid()){
                    if($mediaNames = ChallengeHelper::uploadToS3($data["media"], $data["post_type"], $width, $height)){
                        $data["media"] = $mediaNames["media_name"];
                        $data["thumb"] = $mediaNames["thumb_name"];
                        $paid = ChallengeModel::createChallenge($data, $user);
                        return $this->sendData(["paid" => $paid]);
                    }
                }else{
                    Log::info($request->file("media")->getErrorMessage());
                    return $this->errorArray($request->file("media")->getErrorMessage());
                }
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            Log::info($ex);
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            Log::info($ex);
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
            $savedChallenges = ChallengeHelper::prepareChallenges($challneges, $user["uuid"]);
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
            if($challenge = ChallengeModel::where("uuid", $data["uuid"])->where("owner_id", $user["uuid"])->first()){
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

    public function editPost(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["postId" => "required"]);
            $user = $this->getAuthenticatedUser();
            if($challenge = ChallengeModel::where("uuid", $data["postId"])->where("owner_id", $user["uuid"])->first()){
                $challenge->description = $data["desc"];
                $challenge->save();
                return $this->sendCustomResponse("post edited", 200);
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
            if($mediaNames = ChallengeHelper::uploadToS3($data["media"], $data["post_type"], $data["width"], $data["height"])){
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

    public function pinPost(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["post_id" => "required" ]);
            $user = $this->getAuthenticatedUser();
            if(PinPost::firstOrCreate([
                "user_id" => $user["uuid"],
                "post_id" => $data["post_id"]
            ])){
            return $this->sendCustomResponse("Successfully pinned post", 200);
            }
            return $this->sendCustomResponse();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function getPinPost(Request $request){
        try {
            $user = $this->getAuthenticatedUser();
            return $this->sendCustomResponse(ChallengeHelper::preparePinnedPost($user["uuid"]));
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }
}

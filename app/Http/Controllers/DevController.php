<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class DevController extends Controller
{
    public function saveChallenge(Request $request){
        try {
            $data = $request->all();
            
            echo '<pre>';
            print_r($request->input("owner_id"));
            die();

            $user = User::find($data["owner_id"]);

            $width = isset($data["width"]) ? $data["width"] : 120;
            $height = isset($data["height"]) ? $data["height"] : 120;

            $this->validateData($data, ChallengeModel::$createChallengeRules);

            if(isset($data["already_saved"]) && $data["already_saved"] == "true"){

                $update = ChallengeModel::where("uuid", $data["uuid"])->update([
                    "description" => $data["description"],
                    "category" => $data["category"],
                    "privacy" => $data["privacy"],
                    "is_draft" => false
                ]);
                if($update){
                    return $this->sendCustomResponse("Challenge created", 200);
                }
            }else{
                if($request->file("media")->isValid()){
                    if($mediaNames = ChallengeHelper::uploadToS3($data["media"], $data["post_type"], $width, $height)){
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
}

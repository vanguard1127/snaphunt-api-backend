<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;
use Illuminate\Http\Request;

class Season1Controller extends Controller
{
    public function season1Data(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 0;

            $resp = [];
            $challenges = ChallengeModel::where("type", "season1")->offset($offset)->limit($limit)->orderBy("created_at", "ASC")->get();
            foreach($challenges as $ch){
                $resp[] = [
                    "uuid" => $ch["uuid"],
                    "media" => MediaHelper::getFullURL($ch["media"]),
                    "desc" => $ch["description"],
                    "category" => $ch["category"],
                    "privacy" => $ch["privacy"],
                    "snapoffed" => ChallengeHelper::snapOffByUser($user["uuid"], $ch["uuid"]) ? true : false
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

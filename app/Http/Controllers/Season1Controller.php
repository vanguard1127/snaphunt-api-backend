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
            $challenges = ChallengeModel::where("type", "season1")->offset($offset)->limit($limit)->orderBy("thumb", "ASC")->get();
            $resp = ChallengeHelper::prepareChallenges($challenges, $user["uuid"]);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

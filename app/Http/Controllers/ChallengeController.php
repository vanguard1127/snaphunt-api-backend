<?php

namespace App\Http\Controllers;

use App\Models\ChallengeModel;
use App\Traits\ChallengeTrait;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    use ChallengeTrait;

    public function saveChallenge(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ChallengeModel::$createChallengeRules);
            if($mediaName = $this->uploadToS3($data["media"])){
                $data["media"] = $mediaName;
                ChallengeModel::createChallenge($data, $user["uuid"]);
                return $this->sendCustomResponse("Challenge created", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

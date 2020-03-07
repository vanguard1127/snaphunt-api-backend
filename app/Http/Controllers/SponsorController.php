<?php

namespace App\Http\Controllers;

use App\Models\ChallengeModel;
use App\Traits\SponsorTrait;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    use SponsorTrait;

    public function getSponsorChallenge(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 10;

            $challneges = ChallengeModel::where("type", "sponsor")->where("status", 1)->limit($limit)->offset($offset)->get();
            $savedChallenges = $this->prepareSponsorChallenges($challneges);

            return $this->sendData($savedChallenges);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function getSponsorChallengePosts(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["uuid" => "required"]);
            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 10;

            $challneges = ChallengeModel::where("type", "!=", "sponsor")->where("status", 1)->where("original_post", $data["uuid"])->with("owner")->withCount("claps")->limit($limit)->offset($offset)->get();
            $sponsorPosts = $this->prepareSponsorChallengePosts($challneges);

            return $this->sendData($sponsorPosts);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }
}

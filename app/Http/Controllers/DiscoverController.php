<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\DiscoverTrait;
use App\Traits\MediaTrait;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    use MediaTrait, DiscoverTrait;

    public function searchUser(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["query" => "required"]);
            $users = User::searchUsers($data["query"]);
            return $this->sendData($users);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function searchResults(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["query" => "required"]);
            $resp = [ [ "data" => [], "title" => "USERS" ], ["data" => [], "title" => "CHALLENGES" ] ];
            $resp[0]["data"] = $this->prepareSearchUsers($data["query"]);
            $resp[1]["data"] = $this->prepareSearchCallenges($data["query"]);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function flatUserResults(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["query" => "required"]);
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $resp = $this->prepareFlatUserResult($data["query"], $offset);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

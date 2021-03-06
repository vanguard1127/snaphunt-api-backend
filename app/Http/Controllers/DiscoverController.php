<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\DiscoverTrait;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    use  DiscoverTrait;

    public function searchUser(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["query" => "required"]);
            $users = User::searchUsers($data["query"], $user);
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
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["query" => "required"]);
            $resp = [];
            $userData = $this->prepareSearchUsers($data["query"], $user);
            $challengeData = $this->prepareSearchCallenges($data["query"], $user);

            if(isset($data["detail"]) && $data["detail"] == "true" ){
                $resp = $challengeData;
            }else{
                if($userData){
                    $resp[] = [
                        "data" => $userData,
                        "title" => "USERS"
                    ];
                }
    
                if($challengeData){
                    $resp[] = [
                        "data" => $challengeData,
                        "title" => "CHALLENGES"
                    ];
                }
            }

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
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["query" => "required"]);
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $resp = $this->prepareFlatUserResult($data["query"], $user, $offset);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }

    public function discoverFlatData(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, ["cat_ids" => "required"]);
            $user = $this->getAuthenticatedUser();
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $limit = isset($data["limit"]) ? $data["limit"] : 3;
            $resp = $this->prepareFlatDiscoverData($user, $offset, $limit,$data["cat_ids"]);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage(). $ex->getLine(). $ex->getFile());
        }
    }

    public function discoverData(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $limit = isset($data["limit"]) ? $data["limit"] : 4;
            $resp = $this->prepareDiscoverData($user, $offset, $limit);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage(). $ex->getLine(). $ex->getFile());
        }
    }

    public function categoryData(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["category_id" => "required"]);
            $offset = isset($data["offset"]) ? $data["offset"] : 0;
            $limit = isset($data["limit"]) ? $data["limit"] : 3;
            $resp = $this->prepareCategoryData($user, $data["category_id"],$offset, $limit);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage(). $ex->getLine(). $ex->getFile());
        }
    }
}

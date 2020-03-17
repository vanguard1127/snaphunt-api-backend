<?php

namespace App\Http\Controllers;

use App\Helpers\HuntHelper;
use App\Models\Hunt;
use Illuminate\Http\Request;

class HuntController extends Controller
{
    public function saveHunt(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, [
                "title" => "required",
                "challenges" => "required"
            ]);
            if(Hunt::createHunt($request, $user)){
                return $this->sendCustomResponse("done", 200);
            }
            return $this->sendCustomResponse("Something went wrong");
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function getHunts(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();

            $limit = isset($data["limit"]) ? $data["limit"] : 10;
            $offset = isset($data["offset"]) ? $data["offset"] : 10;

            $data = HuntHelper::prepareHunts($user, $limit, $offset);
            return $this->sendData($data);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }


    public function huntDetail(Request $request){
        try {
            $data = $request->all();
            // $user = $this->getAuthenticatedUser();
            $data = HuntHelper::prepareHuntDetail($data);
            return $this->sendData($data);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getLine().$ex->getFile());
        }
    }

    public function joinHunt(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            HuntHelper::processJoinHunt($data, $user);
            return $this->sendData(["uuid" => $data["hunt_id"]]);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->sendCustomResponse($ex->getMessage());
        }
    }
}

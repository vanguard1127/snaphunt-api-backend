<?php

namespace App\Http\Controllers;

use App\Models\Claps;
use Illuminate\Http\Request;

class ClapsController extends Controller
{
    public function addClap(Request $request){
        try {
            $data = $request->all();
            $this->validateData($data, Claps::$addClapRules);
            $user = $this->getAuthenticatedUser();
            if(Claps::where("post_id", $data["post_id"])->where("user_id", $user["uuid"])->first()){
                // remove clap
                Claps::where("post_id", $data["post_id"])->where("user_id", $user["uuid"])->delete();
            }else{
                Claps::addClap($data["post_id"], $user["uuid"]);
            }
            return $this->sendCustomResponse("Clap Added", 200);
            //return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

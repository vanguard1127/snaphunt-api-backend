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
            if(Claps::addClap($data["post_id"], $user["id"])){
                return $this->sendCustomResponse("Clap Added", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

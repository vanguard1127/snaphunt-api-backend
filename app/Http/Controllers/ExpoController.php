<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpoController extends Controller
{
    public function saveExpoToken(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $user->expo_token = $data["expo_token"];
            $user->save();
            return $this->sendCustomResponse("saved", 200);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getFile().$ex->getLine());
        }
    }
}

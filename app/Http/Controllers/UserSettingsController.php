<?php

namespace App\Http\Controllers;

use App\Models\UserSettings;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function updateSettings(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            if(UserSettings::where("user_id", $user["uuid"])->first() == null){
                // create first
                UserSettings::createRow($user["uuid"]);
            }
            if(UserSettings::updateRow($user["uuid"], $data)){
                return $this->sendCustomResponse("Settings updated", 200);
            }
            return $this->errorArray();
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

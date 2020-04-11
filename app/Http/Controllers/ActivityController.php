<?php

namespace App\Http\Controllers;

use App\Traits\NotificationTrait;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use NotificationTrait;

    public function getActivities(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $activities = $this->notifications($user); 
            return $this->sendData($activities);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getFile().$ex->getLine());
        }
    }
}

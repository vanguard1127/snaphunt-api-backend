<?php

namespace App\Http\Controllers;

use App\Helpers\MediaHelper;
use App\Models\User;
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

    public function onlyActivities(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $activities = [];
            $notifications = $user->notifications()->offset($data["offset"])->limit($data["limit"])->get();
            foreach ($notifications as $notification) {

                if($notification->type != "App\Notifications\FollowNotification" ){
                    $notification->markAsRead();
                    $nData = $notification->data;
                    $sender = User::find($nData["sender_id"]);

                    $senderData = [
                        "id" => $sender["uuid"],
                        "username" => $sender["username"],
                        "name" => $sender["first_name"]. " ". $sender["last_name"],
                        "avatar" => MediaHelper::getFullURL($sender["avatar"])
                    ];

                    $activities[] = [
                        "sender" => $senderData,
                        "data" => $nData,
                        "ts" => $notification->created_at
                    ]; 
                }
            }

            return $this->sendData($activities);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getFile().$ex->getLine());
        }
    }
}

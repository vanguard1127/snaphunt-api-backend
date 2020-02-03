<?php
namespace App\Traits;

use App\Models\Friend;

trait NotificationTrait{

    public function notifications($user){
        $resp = [ "data" => [ [ "title" => "Follow Requests", "data" => []], [ "title" => "Suggesstions", "data" => [] ] ], "count" => 0];
        $count = 0;
        $pendingRequests = Friend::where("following_id", $user["uuid"])->where("status", "pending")->with("follower")->get();

        foreach ($pendingRequests as $request) {
            $resp["data"][0]["data"][] = [
                "avatar" => $this->getFullURL($request->follower["avatar"]),
                "full_name" => $request->follower["first_name"]. " ". $request->follower["last_name"],
                "username" => $request->follower["username"],
                "follower_id" => $request->follower["uuid"]
            ];
            $count++;
        }
        // foreach ($user->unreadNotifications as $notification) {
        //     if($notification->type == "App\Notifications\FollowNotification"){
        //         $resp["data"][0]["data"][] = [
        //             "avatar" => $this->getFullURL($user["avatar"]),
        //             "full_name" => $user["first_name"], " ". $user["last_name"],
        //             "username" => $user["username"]
        //         ];
        //     }
        //     $count++;
        // }
        $resp["count"] = $count;
        return $resp;
    }
}

?>
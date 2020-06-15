<?php
namespace App\Traits;

use App\Helpers\MediaHelper;
use App\Models\Friend;

trait NotificationTrait{

    public function notifications($user){
        $resp = [ "data" => [ [ "title" => "Follow Requests", "data" => []] ], "count" => 0, "unread_count" => 0];
        $count = 0;
        $pendingRequests = Friend::where("following_id", $user["uuid"])->where("status", "pending")->with("follower")->get();

        foreach ($pendingRequests as $request) {
            $resp["data"][0]["data"][] = [
                "avatar" => MediaHelper::getFullURL($request->follower["avatar"]),
                "full_name" => $request->follower["first_name"]. " ". $request->follower["last_name"],
                "username" => $request->follower["username"],
                "follower_id" => $request->follower["uuid"]
            ];
            $count++;
        }
        $resp["count"] = $count + $user->unreadNotifications()->where("type", "!=", "App\Notifications\FollowNotification")->count();
        $resp["unread_count"] = $user->unreadNotifications()->where("type", "!=", "App\Notifications\FollowNotification")->count();

        return $resp;
    }
}

?>
<?php

namespace App\Helpers;

use App\Models\ChallengeModel;
use App\Models\Friend;
use App\Models\UserSettings;

class CommonHelper{

    public static function prepareFriendObj($user, $myUser){
        return [
            "owner" => [
                "id" => $user["uuid"],
                "username" => $user["username"],
                "name" => $user["first_name"]. " ".$user["last_name"],
                "avatar" => MediaHelper::getFullURL($user["avatar"]),
            ],
            "challenges" => self::LastThreeChallenges($user),
            "follow_status" => Friend::getFollowStatus($user["uuid"], $myUser["uuid"]),
            "private" => UserSettings::isPrivate($user["uuid"]),
            "followers_count" => Friend::totalFollowers($user["uuid"]),
            "challenges_count" => ChallengeModel::totalChallenges($user["uuid"])
        ];
    }

    public static function LastThreeChallenges($owner){
        $resp = [];
        $challenges = ChallengeModel::where("owner_id", $owner["uuid"])->orderBy("created_at", "DESC")->limit(3)->get();
        foreach($challenges as $challenge){
            $resp[] = [
                "owner" => [ 
                    "name" => $owner["first_name"]." ".$owner["last_name"],
                    "username" => $owner["username"],
                    "id" => $owner["uuid"],
                    "avatar" => MediaHelper::getFullURL($owner["avatar"])
                ],
                "thumb" => MediaHelper::getFullURL($challenge["thumb"]),
                "media" => MediaHelper::getFullURL($challenge["media"]),
                "desc" => $challenge["description"],
                "post_type" => $challenge["post_type"],
                "claps" => ChallengeHelper::getClapCount($challenge->claps),
                "comments" => $challenge->comments->count(),
                "snapoff_count" => ChallengeHelper::snapOffCount($challenge["uuid"]),
                "uuid" => $challenge["uuid"],
                "category" => $challenge["category"],
                "cat_name" => config("general.categories_admin")[$challenge["category"]],
                "privacy" => $challenge["privacy"],
                "is_snapoff" => $challenge["original_post"] !=null ? true : false,
                "snapoffed" => ChallengeHelper::snapOffByUser($owner["uuid"], $challenge["uuid"])
            ];
        }
        return $resp;
    }

}

?>
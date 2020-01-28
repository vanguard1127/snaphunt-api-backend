<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use App\Models\Friend;
use App\Models\User;
use App\Models\UserSettings;

trait DiscoverTrait{

    public function prepareSearchUsers($query, $myUser){
        $resp = [];
        $users = User::where("username", "ilike", "%$query%")->where("uuid", "!=", $myUser["uuid"])->limit(3)->get();
        foreach($users as $user){
            $resp[] = [
                "uuid" => $user["uuid"],
                "username" => $user["username"],
                "full_name" => $user["first_name"]. " ".$user["last_name"],
                "avatar" => $this->getFullURL($user["avatar"]),
                "follow_status" => Friend::getFollowStatus($user["uuid"], $myUser["uuid"]),
                "private" => UserSettings::isPrivate($user["uuid"]),
                "followers_count" => Friend::totalFollowers($user["uuid"]),
                "challenges_count" => ChallengeModel::totalChallenges($user["uuid"])
            ];
        }
        return $resp;
    }

    public function prepareSearchCallenges($query){
        return [];
    }

    public function prepareFlatUserResult($query, $myUser, $offset = 0, $limit = 15){
        $resp = [];
        $users = User::where("username", "ilike", "%$query%")->where("uuid", "!=", $myUser["uuid"])->offset($offset)->limit($limit)->get();
        foreach($users as $user){
            $resp[] = [
                "uuid" => $user["uuid"],
                "username" => $user["username"],
                "full_name" => $user["first_name"]. " ".$user["last_name"],
                "avatar" => $this->getFullURL($user["avatar"]),
                "challenges" => $this->LastThreeChallenges($user),
                "follow_status" => Friend::getFollowStatus($user["uuid"], $myUser["uuid"]),
                "private" => UserSettings::isPrivate($user["uuid"]),
                "followers_count" => Friend::totalFollowers($user["uuid"]),
                "challenges_count" => ChallengeModel::totalChallenges($user["uuid"])
            ];
        }
        return $resp;
    }

    public function LastThreeChallenges($owner){
        $resp = [];
        $challenges = ChallengeModel::where("owner_id", $owner["uuid"])->orderBy("created_at", "DESC")->limit(3)->get();
        foreach($challenges as $challenge){
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "avatar" => $this->getFullURL($owner["avatar"]),
                "thumb" => $this->getFullURL($challenge["thumb"]),
                "claps" => $challenge->claps->count(),
                "uuid" => $challenge["uuid"]
            ];
        }
        return $resp;
    }
}

?>
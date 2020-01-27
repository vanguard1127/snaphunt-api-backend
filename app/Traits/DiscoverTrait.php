<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use App\Models\User;

trait DiscoverTrait{

    public function prepareSearchUsers($query){
        $resp = [];
        $users = User::where("username", "ilike", "%$query%")->limit(3)->get();
        foreach($users as $user){
            $resp[] = [
                "uuid" => $user["uuid"],
                "username" => $user["username"],
                "full_name" => $user["first_name"]. " ".$user["last_name"],
                "avatar" => $this->getFullURL($user["avatar"])
            ];
        }
        return $resp;
    }

    public function prepareSearchCallenges($query){
        return [];
    }

    public function prepareFlatUserResult($query, $offset = 0, $limit = 15){
        $resp = [];
        $users = User::where("username", "ilike", "%$query%")->offset($offset)->limit($limit)->get();
        foreach($users as $user){
            $resp[] = [
                "uuid" => $user["uuid"],
                "username" => $user["username"],
                "full_name" => $user["first_name"]. " ".$user["last_name"],
                "avatar" => $this->getFullURL($user["avatar"]),
                "challenges" => $this->LastThreeChallenges($user)
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
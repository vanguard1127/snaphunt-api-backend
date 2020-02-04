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

    public function prepareSearchCallenges($query, $myUser){

        $friendIds = Friend::followingIds($myUser["uuid"]);
        $challenges = ChallengeModel::where("description", "ilike", "%$query%")->orWhereHas("owner", function($sql) use($myUser, $query){
            $sql->where("username", "ilike", "%$query%")->where("uuid", "!=", $myUser["uuid"]);
        })->where(function($sql){
            $sql->where("privacy", "public");
        })->orWhere(function($sql) use($friendIds){
            $sql->where("privacy", "friends")->whereIn("owner_id", $friendIds);
        })
        ->orderBy("created_at", "DESC")->get();

        return $this->prepareChallenges($challenges);
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

    public function prepareDiscoverData($offset, $limit, $categoryOffset = 3){
        $resp = [];
        $categories = config("general.categories");
        if($categoryOffset == 3){
            $mostPopular = ChallengeModel::withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
            $catOne = ChallengeModel::where("category", 3)->withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
            $catTwo = ChallengeModel::where("category", 4)->withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
            $resp[] = ["title" => "Most Popular", "data" => $this->prepareChallenges($mostPopular) ];
            $resp[] = ["title" => $categories[3], "data" =>  $this->prepareChallenges($catOne)];
            $resp[] = ["title" => $categories[4], "data" => $this->prepareChallenges($catTwo) ];
        }else{
            for($i=$categoryOffset; $i<=$categoryOffset + 2; $i++){
                $cat = ChallengeModel::where("category", $categoryOffset)->withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
                $resp[] = ["title" => $categories[$categoryOffset], "data" => $this->prepareChallenges($cat) ];
            }
        }
        return $resp;
    }
}

?>
<?php
namespace App\Helpers;

use App\Models\ChallengeModel;
use App\Models\HuntMember;

class HuntHelper{

    public static function prepareHunts($user, $limit, $offset){
        $resp = [];
        $hunts = HuntMember::where("user_id", $user["uuid"])->where("status", "active")->with(["hunt" => function($sql){
            $sql->with("owner")->with(["challenges" => function($sql){
                $sql->where("type", "hunt");
            }]);
        }])->limit($limit)->offset($offset)->get();

        foreach($hunts as $k => $hunt){
            $resp[$k]["title"] = $hunt["hunt"]["title"];
            $resp[$k]["creator"] = $hunt["hunt"]["owner"]["username"];
            $resp[$k]["uuid"] = $hunt["hunt"]["uuid"];
            $resp[$k]["challenges"] = ChallengeHelper::prepareChallenges($hunt["hunt"]["challenges"]);
            $resp[$k]["last_three"] = self::lastThreeSnaps($hunt["hunt_id"]);
        }
        return $resp;
    }

    public static function lastThreeSnaps($huntId){
        $resp = [];
        $totalSnapoffs = ChallengeModel::selectRaw("COUNT(*) AS count")->where("hunt_id", $huntId)->where("original_post", "!=", null)->first();
        // last three
        $lastThreeUsers = ChallengeModel::where("hunt_id", $huntId)->where("original_post", "!=", null)->with("owner")->orderBy("created_at", "desc")->limit(3)->get();
        foreach($lastThreeUsers as $user){
            $resp[] = [
                "avatar" => MediaHelper::getFullURL($user["owner"]["avatar"])
            ];
        }
        return ["users" => $resp, "total" => $totalSnapoffs["count"]];
    }
}

?>
<?php
namespace App\Helpers;

use App\Models\ChallengeModel;
use App\Models\Hunt;
use App\Models\HuntMember;

class HuntHelper{

    public static function prepareHunts($user, $limit, $offset){
        $resp = [];
        $hunts = HuntMember::where("user_id", $user["uuid"])->where("status", "active")->with(["hunt" => function($sql){
            $sql->withCount("members")->with("owner")->with(["challenges" => function($sql){
                $sql->where("type", "hunt");
            }]);
        }])->limit($limit)->offset($offset)->get();

        foreach($hunts as $k => $hunt){
            $resp[$k]["title"] = $hunt["hunt"]["title"];
            // $resp[$k]["members"] = $hunt["hunt"]["members_count"];
            $resp[$k]["creator"] = $hunt["hunt"]["owner"]["username"];
            $resp[$k]["uuid"] = $hunt["hunt"]["uuid"];
            $resp[$k]["challenges"] = ChallengeHelper::prepareHuntChallenges($hunt["hunt"]["challenges"]);
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


    public static function prepareHuntDetail($data, $user){
        $resp = [];
        $hunt = Hunt::where("uuid", $data["uuid"])->with("members")->with(["challenges" => function($sql){
            $sql->where("original_post", null);
        }])->first();

        $resp = [
            "title" => $hunt["title"],
            "members" => self::prepareMembers($hunt["members"]),
            "uuid" => $hunt["uuid"],
            "challenges" => ChallengeHelper::prepareChallenges($hunt["challenges"], $user["uuid"], true),
        ];
        return $resp;
    }

    public static function prepareMembers($members){
        $resp = [];
        foreach($members as $member){
            if($member->status == "active"){
                $resp[] = [
                    "uuid" => $member["user_id"],
                    "username" => $member->user->username,
                    "first_name" => $member->user->first_name,
                    "last_name" => $member->user->last_name,
                    "avatar" =>  MediaHelper::getFullURL($member->user->avatar),
                ];
            }
        }
        return $resp;
    }

    public static function processJoinHunt($data, $user){
        if($row = HuntMember::where("hunt_id", $data["hunt_id"])->where("user_id", $user["uuid"])->first()){
            // update its status
            $row->status = "active";
            $row->save();
        }else{
            HuntMember::create([
                "user_id" => $user["uuid"],
                "hunt_id" => $data["hunt_id"],
                "status" => "active"
            ]);
        }
    }   

    public static function prepareHuntChallengePosts($data){
        $resp = [];
        foreach($data as $challenge){
            $resp[] = [
                "avatar" => MediaHelper::getFullURL($challenge["owner"]["avatar"]),
                // "claps" => $challenge["claps_count"],
                "media" => MediaHelper::getFullURL($challenge["media"]),
                "thumb" => MediaHelper::getFullURL($challenge["thumb"]),
                "owner_name" => $challenge["owner"]["first_name"]. " ". $challenge["owner"]["last_name"],
                "desc" => $challenge["description"],
                "post_type" => $challenge["post_type"],
                "claps" => ChallengeHelper::getClapCount($challenge->claps),
                "comments" => $challenge->comments->count(),
                "uuid" => $challenge["uuid"],
                "category" => $challenge["category"],
                "privacy" => $challenge["privacy"],
                "is_snapoff" => $challenge["original_post"] !=null ? true : false
            ];
        }
        return $resp;
    }
}

?>
<?php
namespace App\Traits;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
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
                "owner" => [
                    "id" => $user["uuid"],
                    "username" => $user["username"],
                    "name" => $user["first_name"]. " ".$user["last_name"],
                    "avatar" => MediaHelper::getFullURL($user["avatar"]),
                ],
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

        return ChallengeHelper::prepareChallenges($challenges, $myUser["uuid"]);
    }

    public function prepareFlatUserResult($query, $myUser, $offset = 0, $limit = 15){
        $resp = [];
        $users = User::where("username", "ilike", "%$query%")->where("uuid", "!=", $myUser["uuid"])->offset($offset)->limit($limit)->get();
        foreach($users as $user){
            $resp[] = [
                "owner" => [
                    "id" => $user["uuid"],
                    "username" => $user["username"],
                    "name" => $user["first_name"]. " ".$user["last_name"],
                    "avatar" => MediaHelper::getFullURL($user["avatar"]),
                ],
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

    public function prepareDiscoverData($user, $offset, $limit, $categoryOffset = 0){
        $resp = [];
        $categories = config("general.categories");
        for($i=$offset; $i <$offset + 4; $i++){
            if(isset($categories[$i+1])){
                if($i == 0){
                    $cat = ChallengeModel::withCount("claps")->orderBy("claps_count", "desc")->offset($categoryOffset)->limit($limit)->get();
                }else{
                    $cat = ChallengeModel::where("category", $i+1)->withCount("claps")->orderBy("claps_count", "desc")->offset($categoryOffset)->limit($limit)->get();
                }
                $resp[] = ["title" => $categories[$i+1], "data" => ChallengeHelper::prepareChallenges($cat, $user["uuid"]), "category" => $i+1 ];
            }
        }
        return $resp;
    }

    public function prepareFlatDiscoverData($user, $offset, $limit, $categoryIds){
        if($categoryIds == "all"){
            $challenges =  ChallengeModel::withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
        }else{
            $catIds = explode(",",$categoryIds);
            $challenges =  ChallengeModel::whereIn("category", $catIds)->withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
        }
        return ChallengeHelper::prepareChallenges($challenges, $user["uuid"]);
    }

    public function prepareCategoryData($user, $categoryId, $offset, $limit){
        $resp = [];
        $categories = config("general.categories");
        if(isset($categories[$categoryId])){
            $cat = ChallengeModel::where("category", $categoryId)->withCount("claps")->orderBy("claps_count", "desc")->offset($offset)->limit($limit)->get();
            $resp = ChallengeHelper::prepareChallenges($cat, $user["uuid"]);
        }
        return $resp;
    }

}

?>
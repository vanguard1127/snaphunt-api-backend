<?php
namespace App\Traits;

use App\Helpers\ChallengeHelper;
use App\Models\ChallengeModel;
use App\Models\Friend;
use App\Models\User;

trait HomeTrait{

    public function prepareHome($data, $user){
        $resp = [];
        $offset = isset($data["offset"]) ? $data["offset"] : 0;
        $limit = isset($data["limit"]) ? $data["limit"] :20;
        $followers = Friend::where("follower_id", $user["uuid"])->pluck("following_id");
        $followers[] = $user["uuid"];
        $posts = ChallengeModel::whereIn("owner_id", $followers)->where("is_draft", false)->where("type", "!=", "hunt")->offset($offset)->limit($limit)->orderBy("created_at", "DESC")->get();
        if(!$posts->isEmpty()){
          $resp = ChallengeHelper::prepareChallenges($posts);
        }
        return $resp;
    }
}

?>
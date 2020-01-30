<?php
namespace App\Traits;

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
        $posts = ChallengeModel::whereIn("owner_id", $followers)->offset($offset)->limit($limit)->orderBy("created_at", "DESC")->get();

        if(!$posts->isEmpty()){
          $resp = $this->prepareChallenges($posts);
        }
        return $resp;
    }
}

?>
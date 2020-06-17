<?php 
namespace App\Helpers;

use App\Models\ChallengeModel;
use Carbon\Carbon;
use Challenge;

class FeaturedHelper{

    public static function prepareFeaturedPosts($data, $user){
        $resp = [];
        $posts = ChallengeModel::with("owner")->where("status", 2)->where("is_featured", true)->whereHas("featured_history", function($sql){
            $sql->where("featured_ends", ">", Carbon::now());
        })->limit($data["limit"])->offset($data["offset"])->orderBy("created_at", "DESC")->get();

        $resp  = ChallengeHelper::prepareChallenges($posts, $user["uuid"]);
        // foreach($posts as $post){
        //     $resp[] = [
        //         "creator" => $post["owner"]["username"],
        //         "desc" => $post["description"],
        //         "completed" => ChallengeModel::snapOffedCount($post["uuid"]),
        //         "claps" => ChallengeHelper::getClapCount($post->claps),
        //         "media" => MediaHelper::getFullURL($post["media"]),
        //         "thumb" => MediaHelper::getFullURL($post["thumb"]),
        //         "post_type" => $post["post_type"],:
        //         "ownerAvatar" => MediaHelper::getFullURL($post["owner"]["thumb"]),
        //         "ownerName" => $post["owner"]["username"],
        //         "comments" => $post->comments->count(),
        //     ];
        // }

        return $resp;
    }
}

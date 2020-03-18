<?php
namespace App\Traits;

use App\Helpers\ChallengeHelper;
use App\Helpers\MediaHelper;
use App\Models\ChallengeModel;

trait SponsorTrait{

    public function prepareSponsorChallenges($challenges){

        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "avatar" => MediaHelper::getFullURL($owner["avatar"]),
                "thumb" => MediaHelper::getFullURL($challenge["thumb"]),
                "media" => MediaHelper::getFullURL($challenge["media"]),
                "desc" => $challenge["description"],
                "title" => $challenge["title"],
                "post_type" => $challenge["post_type"],
                "last_three_snapoff" => ChallengeHelper::lastThreeSnapOff($challenge["uuid"]),
                // "claps" => $this->getClapCount($challenge->claps),
                // "comments" => $challenge->comments->count(),
                "uuid" => $challenge["uuid"],
                "category" => $challenge["category"],
                // "privacy" => $challenge["privacy"],
                // "is_snapoff" => $challenge["original_post"] !=null ? true : false
            ];
        }
        return $resp;
    }

    public function prepareSponsorChallengePosts($data){
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
                "claps" =>  ChallengeHelper::getClapCount($challenge->claps),
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
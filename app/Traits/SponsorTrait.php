<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use Illuminate\Support\Facades\DB;

trait SponsorTrait{

    public function prepareSponsorChallenges($challenges){

        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "avatar" => $this->getFullURL($owner["avatar"]),
                "thumb" => $this->getFullURL($challenge["thumb"]),
                "media" => $this->getFullURL($challenge["media"]),
                "desc" => $challenge["description"],
                "title" => $challenge["title"],
                "post_type" => $challenge["post_type"],
                "last_three_snapoff" => $this->lastThreeSnapOff($challenge["uuid"]),
                // "claps" => $this->getClapCount($challenge->claps),
                // "comments" => $challenge->comments->count(),
                "uuid" => $challenge["uuid"],
                // "category" => $challenge["category"],
                // "privacy" => $challenge["privacy"],
                // "is_snapoff" => $challenge["original_post"] !=null ? true : false
            ];
        }
        return $resp;
    }

    public function getClapCount($claps){
        $resp = [];
        foreach($claps as $clap){
            $resp[$clap["user_id"]] = $claps->count();
        }
        return $resp;
    }

    public function lastThreeSnapOff($chId){
        $resp = [];
        $totalSnapoffs = DB::statement("SELECT COUNT(*) as count from challenges where original_post = '$chId'")["count"];
        // last three
        $lastThreeUsers = ChallengeModel::where("original_post", $chId)->with("owner")->orderBy("created_at", "desc")->limit(3)->get();
        foreach($lastThreeUsers as $user){
            $resp[] = [
                "avatar" => $this->getFullURL($user["owner"]["avatar"])
            ];
        }
        return ["users" => $resp, "total" => $totalSnapoffs];
    }

}

?>
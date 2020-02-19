<?php
namespace App\Traits;

trait CommonTrait{

    public function prepareChallenges($challenges){
        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "avatar" => $this->getFullURL($owner["avatar"]),
                "thumb" => $this->getFullURL($challenge["thumb"]),
                "media" => $this->getFullURL($challenge["media"]),
                "desc" => $challenge["description"],
                "post_type" => $challenge["post_type"],
                "claps" => $this->getClapCount($challenge->claps),
                "comments" => $challenge->comments->count(),
                "uuid" => $challenge["uuid"],
                "category" => $challenge["category"],
                "privacy" => $challenge["privacy"],
                "is_snapoff" => $challenge["original_post"] ? true : false
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

}

?>
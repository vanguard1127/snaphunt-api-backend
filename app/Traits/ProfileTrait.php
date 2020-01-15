<?php
namespace App\Traits;

trait ProfileTrait{

    public function myChallenges($challenges){
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
                "uuid" => $challenge["uuid"]
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
<?php
namespace App\Traits;

trait ProfileTrait{

    public function myChallenges($challenges){
        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "claps" => 93,
                "avatar" => $this->getFullURL($owner["avatar"]),
                "thumb" => $this->getFullURL($challenge["thumb"]),
                "desc" => $challenge["description"]
            ];
        }
        return $resp;
    }

}

?>
<?php
namespace App\Helpers;

use App\Models\ChallengeModel;
use Illuminate\Support\Facades\DB;

class RankHelper{

    public static function prepareRanking($data){
        $engagements = [];
        $resp = [];
        $limit = isset($data["limit"]) ? $data["limit"] : 10;
        $offset = isset($data["offset"]) ? $data["offset"] : 0;
        if(isset($data["categoryId"])){
            $categoryId = $data["categoryId"];
            $engagements  = DB::select("select users.uuid, users.username, users.avatar, users.thumb,
            SUM((select count(*) from claps where challenges.uuid = claps.post_id)
            +
            (select count(*) from comments where challenges.uuid = comments.post_id)) as eng
            
            from challenges inner join users on challenges.owner_id = users.uuid where challenges.status = '1' and category = $categoryId group by challenges.owner_id, users.uuid order by eng desc limit $limit offset $offset");
        }else{
            $engagements  = DB::select("select users.uuid, users.username, users.avatar, users.thumb,
            SUM((select count(*) from claps where challenges.uuid = claps.post_id)
            +
            (select count(*) from comments where challenges.uuid = comments.post_id)) as eng
            
            from challenges inner join users on challenges.owner_id = users.uuid where challenges.status = '1' group by challenges.owner_id, users.uuid order by eng desc limit $limit offset $offset");
        }

        foreach ($engagements as $key => $eng) {
            $resp[] = [
                "userId" => $eng->uuid,
                "username" => $eng->username,
                "total_eng" => $eng->eng,
                "avatar" => MediaHelper::getFullURL($eng->avatar),
                "thumb" => MediaHelper::getFullURL($eng->avatar)
            ];
        }

        return $resp;
    }
}

?>
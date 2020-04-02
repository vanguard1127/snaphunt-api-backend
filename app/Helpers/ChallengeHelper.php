<?php

namespace App\Helpers;

use App\Models\ChallengeModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChallengeHelper
{
    public static function uploadToS3($media, $type, $thumbWidth = 540, $thumbHeight = 340 ){
        try{
            // save media to local disk first
            $mediaName = time().'_' . $media->getClientOriginalName();
            $thumbName = time().'_thumb_' . $media->getClientOriginalName();
            if($type == "video"){
                Storage::disk('local')->put("uploads/".$mediaName, file_get_contents($media), "public");
                // compress media
                MediaHelper::compressVideo($mediaName);
                $thumbName = MediaHelper::generateGif($mediaName, $thumbWidth, $thumbHeight);
                Storage::disk('s3')->put($thumbName, file_get_contents(storage_path("app/uploads/gifs/").$thumbName) , "public");
                Storage::disk('s3')->put($mediaName, file_get_contents(storage_path("app/uploads/compressedData/").$mediaName) , "public");
                // delete both mp4 file and gif
                unlink(storage_path('app/uploads/'.$mediaName));
                unlink(storage_path('app/uploads/compressedData/'.$mediaName));
                unlink(storage_path('app/uploads/gifs/'.$thumbName));
            }else{
                $thumb = MediaHelper::generateImageThumbnail($media, $thumbWidth, $thumbHeight);
                $originalImage = MediaHelper::compressImage($media);
                Storage::disk('s3')->put($thumbName, $thumb, "public");
                Storage::disk('s3')->put($mediaName, $originalImage, "public");
            }
            return [ "media_name" => $mediaName, "thumb_name" => $thumbName ];
        }catch(\Exception $ex){
            Log::info($ex);
            throw $ex;
          //  return $ex->getMessage();
           // return false;
        }
    }


    public static function prepareChallenges($challenges, $userId , $lastThree = false){
        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = [
                "owner_name" => $owner["first_name"]." ".$owner["last_name"],
                "avatar" => MediaHelper::getFullURL($owner["avatar"]),
                "thumb" => MediaHelper::getFullURL($challenge["thumb"]),
                "media" => MediaHelper::getFullURL($challenge["media"]),
                "desc" => $challenge["description"],
                "post_type" => $challenge["post_type"],
                "is_draft" => $challenge["is_draft"],
                "claps" => self::getClapCount($challenge->claps),
                "comments" => $challenge->comments->count(),
                "snapoff_count" => self::snapOffCount($challenge["uuid"]),
                "uuid" => $challenge["uuid"],
                "category" => $challenge["category"],
                "cat_name" => config("general.categories_admin")[$challenge["category"]],
                "privacy" => $challenge["privacy"],
                "is_snapoff" => $challenge["original_post"] !=null ? true : false,
                "last_three" => $lastThree ? self::lastThreeSnapOff($challenge["uuid"]) : [],
                "snapoffed" => ChallengeHelper::snapOffByUser($userId, $challenge["uuid"])
            ];
        }
        return $resp;
    }

    public static function snapOffCount($chId){
        $count = ChallengeModel::selectRaw("COUNT(*) as count")->where("original_post", $chId)->first();
        return $count["count"];
    }   

    public static function prepareHuntChallenges($challenges){
        $resp = [];
        foreach($challenges as $challenge){
            $resp[] = [
                "desc" => $challenge["description"]
            ];
        }
        return $resp;
    }

    public static function getClapCount($claps){
        $resp = [];
        foreach($claps as $clap){
            $resp[$clap["user_id"]] = $claps->count();
        }
        return $resp;
    }

    public static function lastThreeSnapOff($chId){
        $resp = [];
        $totalSnapoffs = ChallengeModel::selectRaw("COUNT(*) AS count")->where("original_post", $chId)->first();
        // last three
        $lastThreeUsers = ChallengeModel::where("original_post", $chId)->with("owner")->orderBy("created_at", "desc")->limit(3)->get();
        foreach($lastThreeUsers as $user){
            $resp[] = [
                "avatar" => MediaHelper::getFullURL($user["owner"]["avatar"])
            ];
        }
        return ["users" => $resp, "total" => $totalSnapoffs["count"]];
    }

    public static function snapOffByUser($userId, $postId){
        return ChallengeModel::where("owner_id", $userId)->where("original_post", $postId)->first();
    }
}

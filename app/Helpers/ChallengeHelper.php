<?php

namespace App\Helpers;

use App\Models\ChallengeModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ChallengeHelper
{
    public static function uploadToS3($media, $type, $thumbWidth = 250, $thumbHeight = 250 ){
        try{
            // save media to local disk first
            $mediaName = time().'_' . $media->getClientOriginalName();
            $thumbName = time().'_thumb_' . $media->getClientOriginalName();
            if($type == "video"){
                Storage::disk('local')->put("uploads/".$mediaName, file_get_contents($media), "public");
                // compress media
                MediaHelper::compressVideo($mediaName);
                // $thumbName = MediaHelper::generateGif($mediaName, $thumbWidth, $thumbHeight);
                $videoFrame = MediaHelper::generateThumb($mediaName);
                self::uploadImageTos3(Image::make(storage_path("app/uploads/").$videoFrame), $mediaName, $thumbName, $thumbWidth, $thumbHeight, "thumb");
                // Storage::disk('s3')->put($thumbName, file_get_contents(storage_path("app/uploads/gifs/").$thumbName) , "public");
                Storage::disk('s3')->put($mediaName, file_get_contents(storage_path("app/uploads/compressedData/").$mediaName) , "public");
                // delete both mp4 file and gif
                unlink(storage_path('app/uploads/'.$mediaName));
                unlink(storage_path('app/uploads/'.$videoFrame));
                unlink(storage_path('app/uploads/compressedData/'.$mediaName));
                // unlink(storage_path('app/uploads/gifs/'.$thumbName));
            }else{
                // $originalImage = MediaHelper::compressImage($media);
                // $thumb = MediaHelper::generateImageThumbnail($media, $thumbWidth, $thumbHeight);
                // Storage::disk('s3')->put($mediaName, $originalImage, "public");
                // Storage::disk('s3')->put($thumbName, $thumb, "public");
                self::uploadImageTos3($media, $mediaName, $thumbName, $thumbWidth, $thumbHeight);
            }

            return [ "media_name" => $mediaName, "thumb_name" => $thumbName ];
        }catch(\Exception $ex){
            Log::info($ex);
            throw $ex;
          //  return $ex->getMessage();
           // return false;
        }
    }

    public static function uploadImageTos3($media, $mediaName, $thumbName, $thumbWidth, $thumbHeight, $type = "full"){
        $originalImage = MediaHelper::compressImage($media);
        $thumb = MediaHelper::generateImageThumbnail($media, $thumbWidth, $thumbHeight);
        if($type == "full"){
            Storage::disk('s3')->put($mediaName, $originalImage, "public");
            Storage::disk('s3')->put($thumbName, $thumb, "public");
        }else{
            Storage::disk('s3')->put($thumbName, $thumb, "public");
        }
    }


    public static function prepareChallenges($challenges, $userId , $lastThree = false){
        $resp = [];
        foreach($challenges as $challenge){
            $owner = $challenge->owner;
            $resp[] = self::singleChallenge($owner, $challenge, $userId, $lastThree);
        }
        return $resp;
    }

    public static function singleChallenge($owner, $challenge, $userId, $lastThree){
        $isSnapoff = $challenge["original_post"] !=null ? true : false;
        $data =  [
            "owner" => User::prepareOwner($owner),
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
            "ch_type" => self::getChallengeType($challenge, $owner),
            "cat_name" => config("general.categories_admin")[$challenge["category"]],
            "privacy" => $challenge["privacy"],
            "is_featured" => $challenge["is_featured"],
            "is_snapoff" => $isSnapoff,
            "last_three" => $lastThree ? self::lastThreeSnapOff($challenge["uuid"]) : [],
            "snapoffed" => ChallengeHelper::snapOffByUser($userId, $challenge["uuid"]),
        ];
        if($isSnapoff){
            $data["original_owner"] = ChallengeModel::getOriginalOwner($challenge["original_post"]);
        }
        return $data;
    }

    public static function getChallengeType($ch, $owner){
        if($ch["category"] == 17 && $owner["type"] == 1){
            return "free";
        }
        return "paid";
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
        if(ChallengeModel::where("owner_id", $userId)->where("original_post", $postId)->first()){
            return true;
        }
        return false;
    }

    public static function preparePinnedPost($userId){
        $posts = ChallengeModel::whereHas("pin_post", function($sql) use($userId){
            $sql->where("user_id", $userId);
        })->get();
        return ChallengeHelper::prepareChallenges($posts, $userId);
    }
}

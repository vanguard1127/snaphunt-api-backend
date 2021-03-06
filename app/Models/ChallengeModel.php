<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;

class ChallengeModel extends Model
{
    use UsesUuid;

    public $table = "challenges";

    protected $fillable = [
        "post_type",
        "owner_id",
        "description",
        "category",
        "privacy",
        "media",
        "status",
        "is_draft",
        "thumb",
        "original_post",
        "type",
        "title",
        "hunt_id",
        "is_featured",
        "featured_duration",
        "featured_ends",
        "featured_url"
    ];

    public function owner(){
        return $this->hasOne("App\Models\User", "uuid", "owner_id");
    }

    public function claps(){
        return $this->hasMany("App\Models\Claps", "post_id", "uuid");
    }

    public function comments(){
        return $this->hasMany("App\Models\Comments", "post_id", "uuid");
    }

    public function pin_post(){
        return $this->hasMany("App\Models\PinPost", "post_id", "uuid");
    }

    public function org_post(){
        return $this->hasOne("App\Models\ChallengeModel", "uuid", "original_post");
    }

    public function featured_history(){
        return $this->hasMany("App\Models\FeaturedHistory", "ch_id", "uuid");
    }


    public static $createChallengeRules = [
        "category" => "required",
        "privacy" => "required",
        "post_type" => "required",
        // "media" => "required"
    ];

    public static function getCategory($category, $type){
        if($category == "null" && $type == "season1"){
            return 17;
        }else if ($category == "null" && $type != "season1"){
            return null;
        }else{
            return $category;
        }
    }

    public static function freeStatus($user){
        $paid = $user["paid"];
        if($user["stripe_id"] == null){
            $challengeCount = ChallengeModel::where("owner_id", $user["uuid"])->where("category", "!=", 17)->get()->count();
            if($challengeCount>=10){
                $paid = false;
                User::where("uuid", $user["uuid"])->update(["paid" => false]);
            }
        }
        return $paid;
    }

    public static function createChallenge($data, $user){

        $userId = $user["uuid"];
        $paid = $user["paid"];
        $obj =  static::create(
            [
                "post_type" => $data["post_type"],
                "owner_id" => isset($data["owner_id"]) ? $data["owner_id"] : $userId,
                "description" => ($data["description"] != "" ? $data["description"] : null),
                "category" => self::getCategory($data["category"], isset($data["type"]) ? $data["type"] : "user"),
                "privacy" => $data["privacy"],
                "media" => $data["media"],
                "is_draft" => $data["is_draft"] == "false" ? 0: 1,
                "thumb" => $data["thumb"],
                "hunt_id" => (isset($data["hunt_id"]) && $data["hunt_id"] != "null") ? $data["hunt_id"] : null,
                "type" => isset($data["type"]) ? $data["type"] : "user",
                "title" => isset($data["title"]) ? $data["title"] : null,
                "original_post" => $data["uuid"] != "null" ? $data["uuid"] : null,
                "is_featured" => $data["is_featured"] == "true" ? 1: 0,
                "featured_url" => $data["featured_url"] == "" ? null : $data["featured_url"],
                "status" => $data["is_featured"] == "true" ? 0 : 1
            ]
        );

        if($data["is_featured"] == "true"){
            FeaturedHistory::create([
                "user_id" => isset($data["owner_id"]) ? $data["owner_id"] : $userId,
                "ch_id" => $obj["uuid"],
                "duration" => $data["featured_duration"],
                "featured_ends" => Carbon::now()->addWeeks($data["featured_duration"])
            ]);
        }

    //    $paid = self::freeStatus($user);

        if($data["uuid"] == "null"){
            // new original challenge getting create
            User::updatePoints($userId, config("general.points.createChallenge"));
        }else{
            $originalCreator = ChallengeModel::select("owner_id")->where("uuid", $data["uuid"])->first();
            User::updatePoints($userId, config("general.points.createChallenge"));
            User::updatePoints($originalCreator["owner_id"], config("general.points.snapoff"));
        }
        return $paid;
    }

    public static function totalChallenges($uuid){
        return static::where("owner_id", $uuid)->get()->count();
    }

    public static function getOriginalOwner($uuid){
        if($ch = self::where("uuid", $uuid)->with("owner")->first()){
            if($ch->owner){
                return User::prepareOwner($ch->owner);

            }
        }
        return null;
    }

    public static function snapOffedCount($chId){
        $count = self::selectRaw("count(*)  as count")->where("original_post", $chId)->first();
        return $count["count"];
    }
}

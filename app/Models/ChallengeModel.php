<?php

namespace App\Models;

use App\Traits\UsesUuid;
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
        "hunt_id"
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

    public static function createChallenge($data, $userId){
        return static::create(
            [
                "post_type" => $data["post_type"],
                "owner_id" => isset($data["owner_id"]) ? $data["owner_id"] : $userId,
                "description" => ($data["description"] != "" ? $data["description"] : null),
                "category" => self::getCategory($data["category"], $data["type"]),
                "privacy" => $data["privacy"],
                "media" => $data["media"],
                "is_draft" => $data["is_draft"],
                "thumb" => $data["thumb"],
                "hunt_id" => (isset($data["hunt_id"]) && $data["hunt_id"] != "null") ? $data["hunt_id"] : null,
                "type" => isset($data["type"]) ? $data["type"] : "user",
                "title" => isset($data["title"]) ? $data["title"] : null,
                "original_post" => $data["uuid"] != "null" ? $data["uuid"] : null
            ]
        );
    }

    public static function totalChallenges($uuid){
        return static::where("owner_id", $uuid)->get()->count();
    }
}

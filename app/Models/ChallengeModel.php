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
        "thumb"
    ];

    public static $createChallengeRules = [
        "media" => "required",
        "category" => "required",
        "privacy" => "required",
        "post_type" => "required"
    ];

    public static function createChallenge($data, $userId){
        return static::create(
            [
                "post_type" => $data["post_type"],
                "owner_id" => $userId,
                "description" => ($data["description"] != "" ? $data["description"] : null),
                "category" => $data["category"],
                "privacy" => $data["privacy"],
                "media" => $data["media"],
                "is_draft" => $data["is_draft"],
                "thumb" => $data["thumb"]
            ]
        );
    }
}

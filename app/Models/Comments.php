<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use UsesUuid;
    
    public $table = "comments";
    protected $fillable = [ "post_id", "user_id", "comment" ];

    public static $addCommentRules = [
        "post_id" => "required",
        "comment" => "required"
    ];

    public static function addComment($postId, $userId, $comment){
        return static::create([ "post_id" => $postId, "user_id" => $userId, "comment" => $comment ]);
    }   
}

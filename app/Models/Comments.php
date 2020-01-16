<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use UsesUuid;
    
    public $table = "comments";
    protected $fillable = [ "post_id", "user_id", "comments" ];

    public static $addCommentRules = [
        "post_id" => "required",
        "comment" => "required"
    ];

    public static $getCommentsRules = [
        "post_id" => "required"
    ];
    
    public static function addComment($data, $userId){
        return static::create([ "post_id" => $data["post_id"], "user_id" => $userId, "comments" => $data["comment"], "created_at" => Carbon::parse($data["ts"]) ]);
    }   

    public function user(){
        return $this->hasOne("App\Models\User", "uuid", "user_id");
    }
}

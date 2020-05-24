<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Claps extends Model
{
    use UsesUuid;
    
    public $table = "claps";
    protected $fillable = [ "post_id", "user_id" ];

    public static $addClapRules = [
        "post_id" => "required"
    ];

    public function user(){
        return $this->belongsTo("App\Models\User", "user_id");
    }

    public static function addClap($postId, $userId){
        return static::create(["post_id" => $postId, "user_id" => $userId]);
    }
}

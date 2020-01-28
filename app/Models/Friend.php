<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use UsesUuid;

    public $table = "friends";

    protected $fillable = [ "following_id", "follower_id", "status" ];

    public static function makeFriends($followingId, $followerId, $status = "pending"){
        return static::create([ "following_id" => $followingId, "follower_id" => $followerId, "status" => $status ]);
    }

    public static function getFollowStatus($followingId, $followerId){

        if($friends = static::where("following_id", $followingId)->where("follower_id", $followerId)->first()){
            return $friends->status;
        }
        return null;
    }

    public static function totalFollowers($uuid){
        return static::where("following_id", $uuid)->where("status","active")->get()->count();
    }
}

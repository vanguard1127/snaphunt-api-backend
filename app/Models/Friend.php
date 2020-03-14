<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use UsesUuid;

    public $table = "friends";

    protected $fillable = [ "following_id", "follower_id", "status" ];

    public function follower(){
        return $this->hasOne("App\Models\User", "uuid", "follower_id");
    }

    public function following(){
        return $this->hasOne("App\Models\User", "uuid", "following_id");
    }

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

    public static function totalFollowings($uuid){
        return static::where("follower_id", $uuid)->where("status","active")->get()->count();
    }

    public static function followingIds($uuid){
        return static::where("follower_id", $uuid)->where("status","active")->pluck("following_id");
    }

    public static function myFriends($uuid, $limit, $offset){
        return static::where(function($sql) use($uuid){
            $sql->where("follower_id", $uuid)
            ->orWhere("following_id", $uuid);
        })->where("status", "active")->limit($limit)->offset($offset)->get();
    }
}

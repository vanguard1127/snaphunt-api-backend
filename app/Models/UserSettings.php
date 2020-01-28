<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use UsesUuid;
    protected $fillable = [
        'stop_all', 'sponsored_alert', 'followers_alert', "user_id", "disable_commenting", "private_account", "save_login", "sync_contacts", "auto_promote"
    ];

    public static function createRow($userId){
        return static::create([ "user_id" => $userId]);
    }

    public static function updateRow($userId, $data){
        return static::where("user_id", $userId)->update($data);
    }

    public static function isPrivate($uuid){
        if($setting = static::where("user_id", $uuid)->first()){
            return $setting["private_account"];
        }
        return false;
    }
}

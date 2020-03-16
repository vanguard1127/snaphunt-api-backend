<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class HuntMember extends Model
{
    use UsesUuid;

    public $table = "hunt_members";

    protected $fillable = [
        "hunt_id",
        "user_id",
        "status"
    ];

    public function hunt(){
        return $this->hasOne("App\Models\Hunt", "uuid", "hunt_id");
    }
}

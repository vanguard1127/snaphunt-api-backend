<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class PinPost extends Model
{
    use UsesUuid;

    public $table = "pin_post";
    protected $fillable = [
        "user_id",
        "post_id"
    ];
}

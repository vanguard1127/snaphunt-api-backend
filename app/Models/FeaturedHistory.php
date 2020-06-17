<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class FeaturedHistory extends Model
{
    use UsesUuid;

    public $table = "featured_history";

    protected $fillable = [
        "user_id",
        "ch_id",
        "duration",
        "featured_ends"
    ];
}

<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class SubProduct extends Model
{
    use UsesUuid;

    public $table = "sub_product";

    protected $fillable = [
        "product_id",
        "stripe_object"
    ];
}

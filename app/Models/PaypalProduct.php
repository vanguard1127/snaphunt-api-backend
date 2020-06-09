<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class PaypalProduct extends Model
{
    use UsesUuid;

    public $table = "paypal_product";

    protected $fillable = [
        "product_id",
        "pp_object"
    ];
}

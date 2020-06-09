<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class PaypalPlan extends Model
{
    use UsesUuid;

    public $table = "paypal_plan";

    protected $fillable = [
        "product_id",
        "plan_id",
        "pp_object"
    ];
}

<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use UsesUuid;

    public $table = "payment_method";
    
    protected $fillable = [
        "user_id",
        "stripe_object",
        "card_token",
        "status",
    ];
}

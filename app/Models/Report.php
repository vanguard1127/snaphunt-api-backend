<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use UsesUuid;

    public $table = 'reports';

    protected $fillable = [
        "reported_by",
        "reported_to",
        "report_type"
    ];
}

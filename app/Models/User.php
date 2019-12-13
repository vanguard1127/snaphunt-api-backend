<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Emadadly\LaravelUuid\Uuids;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'avatar', 'dob', 'password', 'id_code'
    ];

    public static $signUpRules = [ 
        "first_name" => "required",
        "las_name" => "required",
        "username" => "required",
        "email" => "required|email|unique:users,email",
        "username" => "required|unique:users,username",
        "day" => "required",
        "month" => "required",
        "year" => "required",
        "password" => "required"

     ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}

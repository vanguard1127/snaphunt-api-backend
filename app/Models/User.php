<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'avatar', 'dob', 'password', 'id_code', "fb_access_token", "fb_id"
    ];

    public static $signUpRules = [ 
        "first_name" => "required",
        "last_name" => "required",
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function challenges(){
        return $this->hasMany("App\Models\ChallengeModel", "owner_id", "uuid");
    }
    
}

<?php 
namespace App\Traits;

use App\Mail\VerificationEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

trait AuthTrait{

    public function sendVerificationEmail($email, $username, $code){
        Mail::to($email)->send(new VerificationEmail($username, $code));
    }

    public function processFbData($data, $accessToken){
        $user = User::firstOrCreate(
            ['email' => $data["email"]],
            [
            "first_name" => $data["first_name"],
            "last_name" => $data["last_name"],
            "username" => $data["name"],
            "fb_access_token" => $accessToken,
            "dob" => Carbon::parse($data["birthday"]),
            "fb_id" => $data["id"],
            "email" => $data["email"],
            "status" => 1
        ]);
        if($user){
            return JWTAuth::fromUser($user);
        }
        return false;
    }
}
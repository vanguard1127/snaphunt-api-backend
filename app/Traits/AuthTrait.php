<?php 
namespace App\Traits;

use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;

trait AuthTrait{

    public function sendVerificationEmail($email, $username, $code){
        Mail::to($email)->send(new VerificationEmail($username, $code));
    }
}
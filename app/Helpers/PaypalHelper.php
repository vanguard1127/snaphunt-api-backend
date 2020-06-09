<?php 
namespace App\Helpers;

use App\Models\PaypalProduct;

class PaypalHelper{

    // public static function subscribeUser($userId){
    //     $productId = null;
    //     // check if product created
    //     $product = PaypalProduct::first();
    //     if($product){

    //     }
    // }

    // public static function paypalGetRequest($url){

    //     $client = new \GuzzleHttp\Client(['http_errors' => false]);
    //     $headers  = [
    //         "Authorization" => 'Bearer '. env("PAYPAL_TOKEN")
    //     ];
    //     $resp =  $client->request("GET", $url, [ "headers" => $headers]);
    //     return  json_decode($resp->getBody()->getContents(),true);
    // }

    // public static function paypalPostRequest($url, $body){
    //     $client = new \GuzzleHttp\Client(['http_errors' => false]);
    //     $headers  = [
    //         "Authorization" => 'Bearer '. self::paypalAccessToken(),
    //         'Content-Type' => 'application/json'
    //     ];

    //     $resp =  $client->request("POST", $url, [ "headers" => $headers, "body" => $body]);

    //     return  [ "d" => json_decode($resp->getBody()->getContents(),true) ,  "status" => $resp->getStatusCode() ];
    // }

    // public static function paypalAccessToken(){
    //     try{
    //         if($token = Cache::has("PP_AT")){
    //             $client = new \GuzzleHttp\Client(['http_errors' => false]);
    //             $headers  = [
    //                 "Authorization" => 'Bearer '. $token
    //             ];
    //             $resp =  $client->request("GET", env("PAYPAL_URL")."payment-experience/web-profiles", [ "headers" => $headers]);
    //             if($resp->getStatusCode() == 401){
    //                 if($token = self::refreshPaypalToken()){
    //                     return $token;
    //                 }   
    //             }else if($resp->getStatusCode() == 200){
    //                 return $token;
    //             }
    //         }

    //         if($token = self::refreshPaypalToken()){
    //             return $token;
    //         }   
    //         return false;
    //     }catch(\Exception $ex){
    //         return false;
    //     }
    // }

    // public function refreshPaypalToken(){

    //     $client = new \GuzzleHttp\Client(['http_errors' => false]);
    //     $resp =  $client->request("POST", env("PAYPAL_URL")."oauth2/token",
    //         [ "auth" => [
    //             env("PAYPAL_CLIENT_ID"),
    //             env("PAYPAL_SECRET")
    //         ]
    //         , "form_params" => [
    //             "grant_type" => "client_credentials"
    //         ]]);

    //     if($resp->getStatusCode() == 200){
    //         $token = json_decode($resp->getBody()->getContents(),true)["access_token"];
    //         // delete existing token
    //         Cache::forget("PP_AT");
    //         Cache::put("PP_AT", $token);
    //         return $token;
    //     }
    //     return false;
    // }
}

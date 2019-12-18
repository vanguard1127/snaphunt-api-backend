<?php
namespace App\Traits;

trait HttpTrait{

    public function fbGetRequest($accessToken, $endpoint){
        try{
            $url = env("FB_URL").$endpoint."&access_token=".$accessToken;
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $resp =  $client->request("GET", $url);
            if($resp->getStatusCode() == 200){
                return json_decode($resp->getBody()->getContents(),true);
            }
            throw new \Exception("HTTP code was'nt 200 for endpoint". $url);
        }catch(\Exception $ex){
            throw new \Exception($ex);
        }
    }

    public function postRequest(){

    }

}

?>
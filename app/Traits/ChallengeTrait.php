<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ChallengeTrait{

    public function uploadToS3($media){
        try{
            $mediaName = time().'_' . $media->getClientOriginalName();
            Storage::disk('s3')->put($mediaName, file_get_contents($media), "public");
            return $mediaName;
        }catch(\Exception $ex){
          //  return $ex->getMessage();
            return false;
        }
    }

}

?>
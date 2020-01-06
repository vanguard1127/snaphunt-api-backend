<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use Illuminate\Support\Facades\Storage;

trait ChallengeTrait{

    public function uploadToS3($media, $type){
        try{
            // save media to local disk first

            $mediaName = time().'_' . $media->getClientOriginalName();
            $thumbName = time().'_thumb_' . $media->getClientOriginalName();
            if($type == "video"){
                Storage::disk('local')->put("uploads/".$mediaName, file_get_contents($media), "public");
                $thumbName = $this->generateGif($mediaName);
                Storage::disk('s3')->put($thumbName, file_get_contents(storage_path("app/uploads/gifs/").$thumbName) , "public");
                // delete both mp4 file and gif
                unlink(storage_path('app/uploads/'.$mediaName));
                unlink(storage_path('app/uploads/gifs/'.$thumbName));
            }else{
                $thumb = $this->generateImageThumbnail($media);
                Storage::disk('s3')->put($thumbName, $thumb, "public");
            }
            // upload data now
            Storage::disk('s3')->put($mediaName, file_get_contents($media), "public");
            return [ "media_name" => $mediaName, "thumb_name" => $thumbName ];
        }catch(\Exception $ex){
            throw $ex;
          //  return $ex->getMessage();
           // return false;
        }
    }

}

?>
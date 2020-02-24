<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait ChallengeTrait{

    public function uploadToS3($media, $type, $thumbWidth = 200, $thumbHeight = 200 ){
        try{
            // save media to local disk first
            $mediaName = time().'_' . $media->getClientOriginalName();
            $thumbName = time().'_thumb_' . $media->getClientOriginalName();
            if($type == "video"){
                Storage::disk('local')->put("uploads/".$mediaName, file_get_contents($media), "public");
                // compress media
                $this->compressVideo($mediaName);
                $thumbName = $this->generateGif($mediaName, $thumbWidth, $thumbHeight);
                Storage::disk('s3')->put($thumbName, file_get_contents(storage_path("app/uploads/gifs/").$thumbName) , "public");
                Storage::disk('s3')->put($mediaName, file_get_contents(storage_path("app/uploads/compressedData/").$mediaName) , "public");
                // delete both mp4 file and gif
                unlink(storage_path('app/uploads/'.$mediaName));
                unlink(storage_path('app/uploads/compressedData/'.$mediaName));
                unlink(storage_path('app/uploads/gifs/'.$thumbName));
            }else{
                $thumb = $this->generateImageThumbnail($media, $thumbWidth, $thumbHeight);
                $originalImage = $this->compressImage($media);
                Storage::disk('s3')->put($thumbName, $thumb, "public");
                Storage::disk('s3')->put($mediaName, $originalImage, "public");
            }
            return [ "media_name" => $mediaName, "thumb_name" => $thumbName ];
        }catch(\Exception $ex){
            Log::info($ex);
            throw $ex;
          //  return $ex->getMessage();
           // return false;
        }
    }

}

?>
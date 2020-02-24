<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait MediaTrait{

    public function generateImageThumbnail($image){
        return Image::make($image)
        ->resize(null, 200, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg',100);
    }

    public function generateGif($video){
        $gifName = time().".gif";
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path()."/app/uploads/".$video);
        $video->gif(TimeCode::fromSeconds(2), new Dimension(200, 200), 3)->save(storage_path("app/uploads/gifs/").$gifName);
        return $gifName;
    }

    public function compressVideo($videoName){
        //$mediaName = time()."mp4";
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path()."/app/uploads/".$videoName);
        $video
        ->filters()
        ->resize(new Dimension(540, 960))
        ->synchronize();
        $video->save(new X264('libmp3lame','libx264'), storage_path("app/uploads/compressedData/").$videoName);
        return $videoName;
    }

    public function compressImage($image){
        return Image::make($image)
        ->resize(540, 960, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg',70);
    }

    public function getFullURL($media){
        return env("S3_PATH").$media;
    }
}

?>
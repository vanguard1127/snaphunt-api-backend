<?php
namespace App\Helpers;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Intervention\Image\Facades\Image;

class MediaHelper{

    public static function generateImageThumbnail($image, $thumbWidth, $thumbHeight){
        return Image::make($image)
        ->resize($thumbWidth, $thumbHeight, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg',70);
    }

    public static function generateGif($video, $thumbWidth, $thumbHeight){
        $gifName = time().".gif";
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path()."/app/uploads/".$video);
        $video->gif(TimeCode::fromSeconds(2), new Dimension($thumbWidth, $thumbHeight), 3)->save(storage_path("app/uploads/gifs/").$gifName);
        return $gifName;
    }

    public static function generateThumb($video){
        $frameName = time().".png";
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path()."/app/uploads/".$video);
        $frame = $video->frame(TimeCode::fromSeconds(1));
        $frame->save(storage_path("app/uploads/").$frameName);
        return $frameName;
    }

    public static function compressVideo($videoName){
        //$mediaName = time()."mp4";
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open(storage_path()."/app/uploads/".$videoName);
        $video
        ->filters()
        ->resize(new Dimension(540, 960))
        ->synchronize();
        $video->save(new X264('aac', 'libx264'), storage_path("app/uploads/compressedData/").$videoName);
        return $videoName;
    }

    public static function compressImage($image){
        return Image::make($image)->orientate()
        ->resize(540, 960, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg',70);
    }

    public static function getFullURL($media){
        return env("S3_PATH").$media;
    }
}

?>
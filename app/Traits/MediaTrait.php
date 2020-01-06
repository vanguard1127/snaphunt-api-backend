<?php
namespace App\Traits;

use App\Models\ChallengeModel;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
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
}

?>
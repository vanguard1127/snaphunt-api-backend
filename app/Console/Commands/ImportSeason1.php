<?php

namespace App\Console\Commands;

use App\Helpers\ChallengeHelper;
use App\Models\ChallengeModel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Laravel\Lumen\Http\Request;

class ImportSeason1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:import_season1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = fopen(storage_path("app/season1.txt"),'r');
        $user = User::where("type", 1)->first();
        $count = 1;
        while (!feof($file)){
            $line = fgets($file);
            // upload now
            $data = [
                "post_type" => "image",
                "owner_id" => $user['uuid'],
                "description" => trim($line),
                "category" => 17,
                "privacy" => "public",
                "media" => self::pathToUploadedFile(storage_path("app/season1Images/".$count.".jpg")), 
                "status" => 1,
                "is_draft" => false,
                "type" => "season1",
                "title" => null,
                "hunt_id" => null,
                "uuid" => null
            ];
            if($mediaNames = ChallengeHelper::uploadToS3($data["media"], $data["post_type"])){
                $data["media"] = $mediaNames["media_name"];
                $data["thumb"] = $mediaNames["thumb_name"];
                ChallengeModel::createChallenge($data, $user["uuid"]);
                $count++;
                $this->info("done");
            }else{
                $this->error("something went wrong");
            }
        }
    }

    public static function pathToUploadedFile( $path, $public = false )
    {
      $name = File::name( $path );
  
      $extension = File::extension( $path );
  
      $originalName = $name . '.' . $extension;
  
      $mimeType = File::mimeType( $path );
  
      $size = File::size( $path );
  
      $error = null;
  
      $test = $public;
  
      $object = new UploadedFile( $path, $originalName, $mimeType, $size, $error, $test );
  
      return $object;
    }
}

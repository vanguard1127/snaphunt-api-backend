<?php

namespace App\Models;

use App\Helpers\ChallengeHelper;
use App\Traits\ChallengeTrait;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class Hunt extends Model
{
    use UsesUuid;

    public $table = "hunts";

    protected $fillable = [
        "title",
        "status",
        "user_id"
    ];

    public function members(){
        return $this->hasMany("App\Models\HuntMember", "hunt_id", "uuid");
    }

    public function challenges(){
        return $this->hasMany("App\Models\ChallengeModel", "hunt_id", "uuid");
    }

    public function owner(){
        return $this->hasOne("App\Models\User", "uuid", "user_id");
    }

    public static function createHunt($request, $user){
        $data = $request->all();
        DB::beginTransaction();
        try{
            // save hunt
            $hunt = self::create([
                "title" => $data["title"],
                "status" => "active",
                "user_id" => $user["uuid"]
            ]);
            // save challenges
            $challenges = json_decode($data["challenges"], true);

            foreach($challenges as $k => $challenge){
                if(!$request->file("media-".$k)->isValid()){
                    return false;
                }
            }
            foreach($challenges as $k => $challenge){
                $media = $request->file("media-".$k);
                if($mediaNames = ChallengeHelper::uploadToS3($media, $challenge["post_type"], 540, 210)){
                    $challenge["media"] = $mediaNames["media_name"];
                    $challenge["thumb"] = $mediaNames["thumb_name"];
                    $challenge["type"] = "hunt";
                    $challenge["hunt_id"] = $hunt["uuid"];
                    ChallengeModel::createChallenge($challenge, $user["uuid"]);
                }
            }
            // save members
            $members = json_decode($data["members"], true);

            $members[] = $user["uuid"];
            foreach($members as $memberId){
                $status = $memberId == $user["uuid"] ? "active" : "pending";
                HuntMember::create([
                    "hunt_id" => $hunt["uuid"],
                    "status" => $status,
                    "user_id" => $memberId
                ]);
            }
        DB::commit();
        return true;
        }catch(\Exception $ex){
            DB::rollBack();
            throw $ex;
        }
    }
}

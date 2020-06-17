<?php

namespace App\Http\Controllers;

use App\Helpers\FeaturedHelper;
use Illuminate\Http\Request;

class FeaturedController extends Controller
{
    public function featuredPosts(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $resp = FeaturedHelper::prepareFeaturedPosts($data, $user);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage().$ex->getFile().$ex->getLine());
        }
    }
}

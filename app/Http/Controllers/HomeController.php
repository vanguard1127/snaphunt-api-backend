<?php

namespace App\Http\Controllers;

use App\Traits\HomeTrait;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use HomeTrait;

    public function getHome(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $resp = $this->prepareHome($data, $user);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\RankHelper;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function fetchRanking(Request $request){
        try{
            $data = $request->all();
            $resp = RankHelper::prepareRanking($data);
            return $this->sendData($resp);
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->sendCustomResponse($ex->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function report(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            $this->validateData($data, ["reported_to" => "required", "report_type" => "required"]);
            $data["reported_by"] = $user["uuid"];
            if(Report::firstOrCreate($data)){
                return $this->sendCustomResponse("Reported", 200);
            }
            return $this->sendCustomResponse("Something went wrong");
        } catch(ValidationException $ex){
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            return $this->errorArray($ex->getMessage());
        }
    }
}

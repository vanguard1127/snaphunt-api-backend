<?php

namespace App\Http\Controllers;

use App\Helpers\StripeHelper;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function subscribePremium(Request $request){
        try {
            $data = $request->all();
            $user = $this->getAuthenticatedUser();
            DB::beginTransaction();
            if($method = PaymentMethod::firstOrCreate(
                ["user_id" => $user["uuid"]],
                [
                    "user_id" => $user["uuid"],
                    "card_token" => $data["id"],
                    "stripe_object" => json_encode($data)
                ])){
                $customerId = null;
                // check if customer already created
                if($user["stripe_id"]){
                    $customerId = $user["stripe_id"];
                }else{
                    // create new customer first
                    $customer = StripeHelper::createCustomer($user, $method["card_token"]);
                    $customerId = $customer["id"];
                    $user->stripe_id = $customer["id"];
                    $user->stripe_object = json_encode($customer);
                    $user->save();
                }
                // create subscription
                StripeHelper::subscribeCustomer($customerId);
                DB::commit();
                return $this->sendCustomResponse("Subscribed successfully", 200);
            }
            return $this->sendCustomResponse();
        } catch(ValidationException $ex){
            DB::rollBack();
            return $this->validationError($ex);
        }catch (\Exception $ex) {
            DB::rollBack();
            return $this->sendCustomResponse($ex->getMessage().$ex->getFile().$ex->getLine());
        }
    }

    public function webhook(Request $request){
        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($request->all(), true)
            );
            Log::info($event);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            Log::error($e);
            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $invoiceObject = $event->data->object;
                // mark that customer paid
                User::where("stripe_id", $invoiceObject["customer"])->update(["paid" => true]);
                break;
            case 'invoice.payment_failed':
                $invoiceObject = $event->data->object;
                // mark that customer paid
                User::where("stripe_id", $invoiceObject["customer"])->update(["paid" => false]);
                break;
            default:
                // Unexpected event type
                http_response_code(400);
                exit();
        }

        http_response_code(200);

    }
}

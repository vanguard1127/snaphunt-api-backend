<?php 
namespace App\Helpers;

use App\Models\SubProduct;

class StripeHelper{

    public static function getStripe(){
        return new \Stripe\StripeClient(env("STRIPE_KEY"));
    }

    public static function createCustomer($user, $token){
        $stripe = self::getStripe();
        $customer = $stripe->customers->create([
            'email' => $user["email"],
            "name" => $user["name"],
            'source' => $token,
        ]);
        return $customer;
    }

    public static function alreadySubscribed($customerId){
        $stripe = self::getStripe();
        $customer = $stripe->customers->retrieve(
            $customerId,
            []
          );
         if(!empty($customer["subscriptions"]["data"])){
             return true;
         }
         return false;
    }

    public static function subscribeCustomer($customerId){
        if(!self::alreadySubscribed($customerId)){
            $stripe = self::getStripe();
            $prod = null;
            if($dbProd = SubProduct::first()){
                $prod = $dbProd["product_id"];
            }else{
                $stripeProduct = $stripe->products->create([
                    'name' => 'sh_monthly',
                    ]);
                SubProduct::create([ "product_id" => $stripeProduct["id"], "stripe_object" => json_encode($stripeProduct) ]);
                $prod = $stripeProduct["id"];
            }
            return $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [
                    [
                        "price_data" => [
                            "currency" => "CAD",
                            "product" => $prod,
                            "recurring" => [
                                "interval" => "month",
                                "interval_count" => 1
                            ],
                            "unit_amount_decimal" => 299
                        ]
                    ]
                ],
              ]);
        }
    }
}

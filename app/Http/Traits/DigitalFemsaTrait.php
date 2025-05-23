<?php

namespace App\Http\Traits;
use Carbon\Carbon;

trait DigitalFemsaTRait {
    public static function createOrder($event_name, $totalToPay, $order, $discounts, $model_payment) {
        $date = Carbon::now();
        $expiration = (new \DateTime($date->addDays(2)->format('Y-m-d 23:59:59')))->getTimestamp();
        
        $apiKey = env("DIGITALFEMSA_API_KEY");
        // Configure Bearer authorization: bearerAuth
        $config = \DigitalFemsa\Configuration::getDefaultConfiguration()->setAccessToken($apiKey);

        $apiInstance = new \DigitalFemsa\Api\OrdersApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $subtotal   = $totalToPay;
        $discount   = round($totalToPay * $discounts['discount']);
        $total      = $subtotal - $discount;
        $commission = $model_payment == 'separated' ? round($total * .12) : 0;
        $totalToPay = $total + $commission;
        
        $order_request = new \DigitalFemsa\Model\OrderRequest(
                array(
                "line_items" => array(
                    array(
                    "name" => 'Compra de boletos para '.$event_name,
                    "unit_price" => $totalToPay * 100,
                    "quantity" => 1
                    )//first line_item
                ), //line_items
                "currency" => "MXN",
                "customer_info" => array(
                    "name" => $order['name'],
                    "email" => $order['email'],
                    "phone" => $order['phone']
                ), //customer_info
                "charges" => array(
                    array(
                        "payment_method" => array(
                            "type" => "oxxo_cash",
                            "expires_at" => $expiration
                        )//payment_method
                    ) //first charge
                ) //charges
            //order
        )); // \DigitalFemsa\Model\OrderRequest | requested field for order
        $accept_language = 'es'; // string | Use for knowing which language to use
        // $x_child_company_id = '6441b6376b60c3a638da80af'; // string | In the case of a holding company, the company id of the child company to which will process the request.
        
        try {
            $result = $apiInstance->createOrder($order_request, $accept_language);
            return [
                'success'    => true,
                'reference'  => $result['charges']['data'][0]['payment_method']['reference'],
                'totalToPay' => $totalToPay
            ];
        } catch (\Exception $e) {
            return  ['success' => false, 'msj' => $e->getMessage()];
        }
    }
}
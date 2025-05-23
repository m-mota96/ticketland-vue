<?php

namespace App\Http\Traits;
use Conekta\ApiException;
use Conekta\Configuration;
use Conekta\Model\Customer;
use Conekta\Api\CustomersApi;
use Conekta\Api\OrdersApi;

trait ConektaPaymentTrait {
    protected static $config;
    protected static $accept_language;

    protected static function initConekta() {
        if (!self::$config || !self::$accept_language) {
            // Configure Bearer authorization: bearerAuth
            $apiKey = env("CONEKTA_API_KEY");

            self::$config = Configuration::getDefaultConfiguration()->setAccessToken($apiKey);

            self::$accept_language = 'es';
        }
    }

    public static function createCustomer($order) {
        self::initConekta();

        $apiInstance = new CustomersApi(
            new \GuzzleHttp\Client(),
            self::$config
        );

        $customer = new Customer([
            "name" => $order['name'],
            "email" => $order['email'],
            "payment_sources" => [
                [
                    "type" => "card",
                    "token_id" => $order['token_id']
                ]
            ]
        ]); // \Conekta\Model\Customer | requested field for customer

        try {
            $result = $apiInstance->createCustomer($customer, self::$accept_language);
            return [
                'success'     => true,
                'customer_id' => $result['id']
            ];
        } catch (ApiException $e) {
            // dd('Exception when calling CustomersApi->createCustomer: '.$e->getMessage());
            $logFile = fopen("logs/log_customer.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear cliente: ".$e->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }

    public static function createOrder($event_name, $totalToPay, $customer_id, $discounts, $model_payment) {
        self::initConekta();

        $apiInstance = new OrdersApi(
            new \GuzzleHttp\Client(),
            self::$config
        );

        $subtotal   = $totalToPay;
        $discount   = round($totalToPay * $discounts['discount']);
        $total      = $subtotal - $discount;
        $commission = $model_payment == 'separated' ? round($total * .12) : 0;
        $totalToPay = $total + $commission;
        // dd($subtotal.' / '.$discount.' / '.$total.' / '.$commission.' / '.$totalToPay);
        
        // $totalToPay = $totalToPay + ($totalToPay * .12);
        // $discount   = [];
        // if ($discounts['code'] && $discounts['discount'] != 0) {
        //     $offert     = $totalToPay * $discounts['discount'];
        //     $discount = [
        //         [
        //             "amount" => round($offert) * 100,
        //             "code"   => $discounts['code'],
        //             "type"   => "coupon"
        //         ]
        //     ];
        // }

        $order_request = new \Conekta\Model\OrderRequest([
            "amount"=> $totalToPay,
            "line_items" => [
                [
                    "name" => 'Compra de boletos para '.$event_name,
                    "unit_price" => $totalToPay * 100, //se multiplica por 100 conekta
                    "quantity" => 1
                ] //first line_item
            ], //line_items
            // "discount_lines" => $discount,
            "currency" => "MXN",
            "customer_info" => [
                "customer_id" => $customer_id
            ], //customer_info
            "charges" => [
                [
                    "payment_method" => [
                            "type" => "default"
                    ]
                ] //first charge
            ] //charges
        ]); // \Conekta\Model\OrderRequest | requested field for order
        
        try {
            $result = $apiInstance->createOrder($order_request, self::$accept_language);
            return [
                'success' => true,
                'order_id' => $result['charges']['data'][0]['order_id']
            ];
        } 
        catch (\Exception $e) {
            $message = 'Error desconocido';
            if ($e->getResponseObject()) {
                $response = $e->getResponseObject();
                // Verifica que haya detalles
                if (method_exists($response, 'getDetails') && is_array($response->getDetails())) {
                    $details = $response->getDetails();
                    if (!empty($details) && method_exists($details[0], 'getMessage')) {
                        $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
                        fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".$details[0]->getMessage()."\n") or die("Error escribiendo en el archivo");
                        fclose($logFile);
                        return [
                            'success' => false,
                            'msj'     => $details[0]->getMessage() // Mensaje en español
                        ];
                    }
                    $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
                    fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".$e->getMessage()."\n") or die("Error escribiendo en el archivo");
                    fclose($logFile);
                    return [
                        'success' => false,
                        'msj'     => 'Hubo un problema al procesar su pago, por favor verifique su información.'
                    ];
                }
                $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
                fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".$e->getMessage()."\n") or die("Error escribiendo en el archivo");
                fclose($logFile);
                return [
                    'success' => false,
                    'msj'     => 'Hubo un problema al procesar su pago, por favor verifique su información.'
                ];
            }
            $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".$e->getMessage()."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return [
                'success' => false,
                'msj'     => 'Hubo un problema al procesar su pago, por favor verifique su información.'
            ];
        }
    }
}
<?php

namespace App\Http\Traits;
use GuzzleHttp\Exception\RequestException;
use App\Models\ConektaCustomer;
use Conekta\ApiException;
use Conekta\Configuration;
use Conekta\Api\CustomersApi;
use Conekta\Api\OrdersApi;
use Conekta\Model\Customer;
use Conekta\Model\OrderRequest;

trait ConektaPaymentTrait {
    private static $config;
    private static $accept_language;

    private static function initConekta() {
        if (!self::$config || !self::$accept_language) {
            $apiKey = env("CONEKTA_API_KEY");
            self::$config = Configuration::getDefaultConfiguration()->setAccessToken($apiKey);
            self::$accept_language = 'es';
        }
    }

    public static function createOrder($event_name, $total, $order) {
        self::initConekta();

        $customer = ConektaCustomer::where('email', $order['email'])->first();
        if ($customer) {
            $customerId = $customer->customer_id;
        } else {
            $info = self::createCustomer($order);
            if (!$info['success']) {
                return [
                    'success' => false,
                    'msj'     => $info['msj']
                ];
            }
            $customerId = $info['customer_id'];
            ConektaCustomer::create([
                'customer_id' => $customerId,
                'email'       => $order['email'],
                'name'        => $order['name'],
                'phone'       => $order['phone'],
            ]);
        }
        
        $apiInstance = new OrdersApi(
            new \GuzzleHttp\Client(),
            self::$config
        );

        try {
            $order_request = new OrderRequest([
                "currency"      => "MXN",
                "customer_info" => [
                    "customer_id" => $customerId
                ],
                "line_items" => [
                    [
                        "name"       => 'Compra de boletos para '.$event_name,
                        "quantity"   => 1,
                        "unit_price" => $total * 100
                    ]
                ],
                "charges" => [
                    [
                        "payment_method" => [
                            "type"     => "card",
                            "token_id" => $order['token_id']
                        ]
                    ]
                ],
                "antifraud_info" => [
                    "client_ip"          => request()->ip(),
                    "device_fingerprint" => $order['device_session_id']
                ],
                "metadata" => [
                    "ip"         => request()->ip(),
                    "event"      => $event_name,
                    "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            ]);
        
            $result = $apiInstance->createOrder($order_request, self::$accept_language);
            return [
                'success'  => true,
                'order_id' => $result['id']
            ];
        } catch (ApiException $e) {
            $log  = '';
            $msj  = '';
            $body = json_decode($e->getResponseBody(), true);
            if (isset($body['details'])) {
                foreach ($body['details'] as $error) {
                    $log .= $error['message'].'. ';
                    $msj .= $error['message'].'<br>';
                }
            } else {
                $log = $e->getMessage();
                $msj = 'Error al procesar el pago.<br>Si el problema persiste contacta al organizador del evento.';
            }
            $log = trim($log, '. ').' Cliente: '.$order['name'].', Correo: '.$order['email'].', Tarjeta: '.$order['card'].', IP: '.request()->ip();
            $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".$log."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false, 'msj' => trim($msj, '<br>')];
        }
    }

    private static function createCustomer($order) {
        $apiInstance = new CustomersApi(
            new \GuzzleHttp\Client(),
            self::$config
        );
        
        try {
            $customer = new Customer([
                "name"  => $order['name'],
                "email" => $order['email'],
                "phone" => $order['phone'],
            ]);
            $result = $apiInstance->createCustomer($customer, self::$accept_language);

            return [
                'success'     => true,
                'customer_id' => $result['id']
            ];
        } catch (ApiException $e) {
            $log  = '';
            $msj  = '';
            $body = json_decode($e->getResponseBody(), true);
            if (isset($body['details'])) {
                foreach ($body['details'] as $error) {
                    $log .= $error['message'].'. ';
                    $msj .= $error['message'].'<br>';
                }
            } else {
                $log = $e->getMessage();
                $msj = 'Error al crear el cliente.<br>Si el problema persiste contacta al organizador del evento.';
            }
            $logFile = fopen("logs/log_customer.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear cliente: ".trim($log, '. ')."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return ['success' => false, 'msj' => trim($msj, '<br>')];
        }
    }
}
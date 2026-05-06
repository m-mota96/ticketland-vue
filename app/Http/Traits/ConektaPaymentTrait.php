<?php

namespace App\Http\Traits;
use GuzzleHttp\Exception\RequestException;
use App\Models\ConektaCustomer;

trait ConektaPaymentTrait {
    public static function createOrder($event_name, $total, $order) {
        $apiKey = env("CONEKTA_API_KEY");

        $customer = ConektaCustomer::where('email', $order['email'])->first();
        if ($customer) {
            $customerId = $customer->customer_id;
        } else {
            $info = self::createCustomer($apiKey, $order);
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
        
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('POST', 'https://api.conekta.io/orders', [
                'json' => [
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
                    "charges" =>[
                        [
                            "payment_method" => [
                                "type"     => "card",
                                "token_id" => $order['token_id']
                            ]
                        ]
                    ],
                    "metadata" => [
                        "ip"         => $_SERVER['REMOTE_ADDR'],
                        "event"      => $event_name,
                        "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                    ]
                ],
                'headers' => [
                    'Accept-Language' => 'es',
                    'accept'          => 'application/vnd.conekta-v2.2.0+json',
                    'authorization'   => 'Bearer '.$apiKey,
                ],
            ]);
            $body   = $response->getBody()->getContents();
            $data   = json_decode($body);

            return [
                'success' => true,
                'order_id' => $data->charges->data[0]->order_id
            ];
        } catch(RequestException $e) {
            $error = null;
            if ($e->hasResponse()) {
                $error = json_decode(
                    $e->getResponse()->getBody()->getContents(),
                    true
                );
            }
            $msj     = !$error ? 'Error al procesar el pago.<br>Por favor verifica la información de tu tarjeta.' : $error['details'][0]['message'];
            $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".($msj.' Cliente: '.$order['name'].', Correo: '.$order['email'].', Tarjeta: '.$order['card'].', IP: '.$_SERVER['REMOTE_ADDR'] ?? 'Error inesperado.')."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return  ['success' => false, 'msj' => $msj];
        }
    }

    private static function createCustomer($apiKey, $order) {
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('POST', 'https://api.conekta.io/customers', [
                'json' => [
                    "corporate" => false,
                    "name"      => $order['name'],
                    "email"     => $order['email'],
                    "phone"     => $order['phone']
                ],
                'headers' => [
                    'Accept-Language' => 'es',
                    'accept'          => 'application/vnd.conekta-v2.2.0+json',
                    'authorization'   => 'Bearer '.$apiKey,
                ],
            ]);
            $body   = $response->getBody()->getContents();
            $data   = json_decode($body);

            return [
                'success'     => true,
                'customer_id' => $data->id
            ];
        } catch(RequestException $e) {
            $error = null;
            if ($e->hasResponse()) {
                $error = json_decode(
                    $e->getResponse()->getBody()->getContents(),
                    true
                );
            }
            $msj     = !$error ? 'Error al crear el cliente.<br>Por favor verifica que tus datos sean correctos.' : $error['details'][0]['message'];
            $logFile = fopen("logs/log_customer_conekta.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear el cliente: ".($msj.' Cliente: '.$order['name'].', Correo: '.$order['email'].', Tarjeta: '.$order['card'].', IP: '.$_SERVER['REMOTE_ADDR'] ?? 'Error inesperado.')."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return  ['success' => false, 'msj' => $msj];
        }
    }
}
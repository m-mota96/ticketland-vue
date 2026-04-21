<?php

namespace App\Http\Traits;
use GuzzleHttp\Exception\RequestException;

trait ConektaPaymentTrait {
    public static function createOrder($event_name, $total, $order) {
        $apiKey = env("CONEKTA_API_KEY");
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->request('POST', 'https://api.conekta.io/orders', [
                'json' => [
                    "currency"      => "MXN",
                    "customer_info" => [
                        "name"  => $order['name'],
                        "email" => $order['email'],
                        "phone" => $order['phone']
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
            $msj = !$error ? 'Error al procesar el pago.<br>Por favor verifica la información de tu tarjeta.' : $error['details'][0]['message'];
            $logFile = fopen("logs/log_payment.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al procesar el pago: ".($error['details'][0]['message'] ?? 'Error inesperado.')."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return  ['success' => false, 'msj' => $msj];
        }
    }
}
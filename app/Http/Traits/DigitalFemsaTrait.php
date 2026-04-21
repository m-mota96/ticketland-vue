<?php

namespace App\Http\Traits;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;

trait DigitalFemsaTrait {
    public static function createOrder($event_name, $total, $order) {
        $apiKey     = env("DIGITALFEMSA_API_KEY");
        $date       = Carbon::now();
        $expiration = (new \DateTime($date->addDays(2)->format('Y-m-d 23:59:59')))->getTimestamp();
        $client     = new \GuzzleHttp\Client();
        
        try {
            $response = $client->request('POST', 'https://api.digitalfemsa.io/orders', [
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
                            "unit_price" => $total * 100,
                            "quantity"   => 1
                        ]
                    ],
                    "charges" => [
                        [
                            "payment_method" => [
                                "type"       => "oxxo_cash",
                                "expires_at" => $expiration
                            ],
                            "amount" => $total * 100
                        ]
                    ]
                ],
                'headers' => [
                    'Accept-Language' => 'es',
                    'accept'          => 'application/vnd.app-v2.1.0+json',
                    'authorization'   => 'Bearer '.$apiKey,
                ],
            ]);
            $body   = $response->getBody()->getContents();
            $data   = json_decode($body);
            $image  = file_get_contents($data->charges->data[0]->payment_method->barcode_url);
            $base64 = 'data:image/png;base64,' . base64_encode($image);
            return [
                'success'    => true,
                'reference'  => $data->charges->data[0]->payment_method->reference,
                'totalToPay' => $total,
                'order_id'   => $data->charges->data[0]->order_id,
                'bar_code'   => $base64,
                'name_event' => $event_name
            ];
        } catch(RequestException $e) {
            $error = null;
            if ($e->hasResponse()) {
                $error = json_decode(
                    $e->getResponse()->getBody()->getContents(),
                    true
                );
            }
            $msj = !$error ? 'Error al generar su referencia de pago.<br>Si el problema persiste contacta al organizador del evento.' : $error['details'][0]['message'];
            $logFile = fopen("logs/log_femsa.txt", 'a') or die("Error creando archivo");
            fwrite($logFile, date("d/m/Y H:i:s")." Error al crear referencia de pago: ".($error['details'][0]['message'] ?? 'Error inesperado.')."\n") or die("Error escribiendo en el archivo");
            fclose($logFile);
            return  ['success' => false, 'msj' => $msj];
        }
    }
}
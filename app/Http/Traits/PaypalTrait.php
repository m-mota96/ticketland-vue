<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Models\PaypalToken;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Models\OAuthToken;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\OrderApplicationContext;
use PaypalServerSdkLib\Environment;

trait PaypalTrait {

    private $client;

    public function __construct() {
        $clientId = env('PAYPAL_CLIENT_ID');
        $secret   = env('PAYPAL_SECRET');
        $mode     = env('PAYPAL_MODE', 'sandbox');

        $authBuilder = ClientCredentialsAuthCredentialsBuilder::init(
            $clientId,
            $secret
        );

        // Si el token ya caduco se crea uno nuevo y se guarda en la DB
        $authBuilder->oAuthOnTokenUpdate(function (OAuthToken $oAuthToken): void {
            PaypalToken::create([
                'access_token'     => $oAuthToken->getAccessToken(),
                'token_type'       => $oAuthToken->getTokenType(),
                'expires_in'       => $oAuthToken->getExpiresIn(),
                'created_at_token' => date('Y-m-d H:i:s'),
            ]);
        });

        // Crear cliente PayPal
        $this->client = PaypalServerSdkClientBuilder::init()
        ->environment(Environment::SANDBOX)
        ->clientCredentialsAuthCredentials($authBuilder)
        ->build();
    }

    public function createOrder(Request $request) {
        $applicationContext = OrderApplicationContext::init([
            'brandName' => 'Maxwell',
            'landingPage' => 'NO_PREFERENCE',
            'shippingPreference' => 'NO_SHIPPING',
            'userAction' => 'PAY_NOW',
            'locale' => 'es'
        ])->build();

        $collect = [
            'body' => OrderRequestBuilder::init(
                CheckoutPaymentIntent::CAPTURE,
                [
                    PurchaseUnitRequestBuilder::init(
                        AmountWithBreakdownBuilder::init(
                            'MXN',
                            $request->amount
                        )->build()
                    )->build()
                ],
                $applicationContext
            )
            ->setApplicationContext([
                'brand_name' => 'Maxwell',
                'landing_page' => 'NO_PREFERENCE',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW'
            ])
            ->build(),
            // 'prefer' => 'return=minimal'
        ];

        $apiResponse = $this->client->getOrdersController()->createOrder($collect);

        if ($apiResponse->isSuccess()) {
            $order = $apiResponse->getResult();
            return response()->json([
                'order_id' => $order->getId()
            ]);
        } else {
            $errors = $apiResponse->getResult();
            return [
                'success' => false,
                'msj'     => $errors
            ];
        }
    }

    public function captureOrder($orderId) {
        $collect = [
            'id' => $orderId,
            'prefer' => 'return=minimal'
        ];

        $apiResponse = $this->client->getOrdersController()->captureOrder($collect);

        if ($apiResponse->isSuccess()) {
            $order = $apiResponse->getResult();
            if ($order->getStatus() === 'COMPLETED') {
                return [
                    'success'  => true,
                    'order_id' => $order->getId()
                ];
            }
            // dd($order);
            // return [
            //     'success' => false,
            //     'msj'     => 'Tu pago no pudo ser procesado.<br>Por favor intenta con otra tarjeta o método de pago.'
            // ];
        } else {
            $errors = $apiResponse->getResult();
            $responseBody = json_decode(json_encode($apiResponse->getResult()), true);
            $description = $responseBody['details'][0]['description'] ?? $responseBody['message'] ?? 'Tu pago no pudo ser procesado.<br>Por favor intenta con otra tarjeta o método de pago.';
            return [
                'success' => false,
                'msj'     => $description
            ];
        }
    }
}
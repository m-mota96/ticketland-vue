<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payment_methods[] = [
            'name'       => 'PayPal (Internacional)',
            'sku'        => 'paypal',
            'commission' => 0.12,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $payment_methods[] = [
            'name'       => 'Tarjeta de Débito/Crédito (México)',
            'sku'        => 'card',
            'commission' => 0.12,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $payment_methods[] = [
            'name'       => 'Pago en Oxxo (México)',
            'sku'        => 'oxxo',
            'commission' => 0.12,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        PaymentMethod::insert($payment_methods);
    }
}

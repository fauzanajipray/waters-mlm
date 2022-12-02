<?php

namespace Database\Seeders;

use App\Models\Configuration;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuration::updateOrCreate([
            'key' => 'activation_payment_expiration',
        ],
        [
            'key' => 'activation_payment_expiration',
            'value' => '1',
            'description' => 'Payment expiration time',
        ]);

        Configuration::updateOrCreate([
            'key' => 'activation_payment_amount',
        ],[
            'key' => 'activation_payment_amount',
            'value' => '100000',
            'description' => 'Payment amount',
        ]);

        Configuration::updateOrCreate([
            'key' => 'transaction_demokit_discount_percentage',
        ],[
            'key' => 'transaction_demokit_discount_percentage',
            'value' => '50',
            'description' => 'Transaction demokit discount percentage',
        ]);

        Configuration::updateOrCreate([
            'key' => 'transaction_diplay_discount_amount',
        ],[
            'key' => 'transaction_display_discount_amount',
            'value' => '50',
            'description' => 'Transaction display discount amount',
        ]);
    }
}

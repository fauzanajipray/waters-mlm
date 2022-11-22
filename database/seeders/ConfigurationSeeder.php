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
        Configuration::create([
            'key' => 'activation_payment_expiration',
            'value' => '1',
            'description' => 'Payment expiration time',
        ]);

        Configuration::create([
            'key' => 'activation_payment_amount',
            'value' => '100000',
            'description' => 'Payment amount',
        ]);
    }
}

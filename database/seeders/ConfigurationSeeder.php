<?php

namespace Database\Seeders;

use App\Models\Configuration;
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

        Configuration::updateOrCreate([
            'key' => 'bonus_tax_percentage_non_npwp',
        ],[
            'key' => 'bonus_tax_percentage_non_npwp',
            'value' => '6',
            'description' => 'Bonus tax percentage for non npwp',
        ]);

        Configuration::updateOrCreate([
            'key' => 'bonus_tax_percentage_npwp',
        ],[
            'key' => 'bonus_tax_percentage_npwp',
            'value' => '5',
            'description' => 'Bonus tax percentage for npwp',
        ]);

        Configuration::updateOrCreate([
            'key' => 'bonus_cabang_percentage_for_product',
        ],[
            'key' => 'bonus_cabang_percentage_for_product',
            'value' => '4.1945',
            'description' => 'Bonus cabang percentage for selling product',
        ]);

        Configuration::updateOrCreate([
            'key' => 'bonus_stokist_percentage_for_product',
        ],[
            'key' => 'bonus_stokist_percentage_for_product',
            'value' => '2.097',
            'description' => 'Bonus stokist percentage for selling product',
        ]);

        Configuration::updateOrCreate([
            'key' => 'date_start_komisi_lsi',
        ],[
            'key' => 'date_start_komisi_lsi',
            'value' => '2023-02-01',
            'description' => 'Tanggal mulai komisi LSI',
        ]);

        Configuration::updateOrCreate([
            'key' => 'date_start_komisi_pm',
        ],[
            'key' => 'date_start_komisi_pm',
            'value' => '2023-02-01',
            'description' => 'Tanggal mulai komisi PM',
        ]);

    }
}

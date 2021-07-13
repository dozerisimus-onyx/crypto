<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
            ['code' => 'AUD', 'name' => 'Australian Dollar'],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev'],
            ['code' => 'BRL', 'name' => 'Brazilian Real'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar'],
            ['code' => 'CHF', 'name' => 'Swiss Franc'],
            ['code' => 'CNY', 'name' => 'Chinese Yuan'],
            ['code' => 'COP', 'name' => 'Colombian Peso'],
            ['code' => 'CZK', 'name' => 'Czech Koruna'],
            ['code' => 'DKK', 'name' => 'Danish Krone'],
            ['code' => 'DOP', 'name' => 'Dominican Peso'],
            ['code' => 'EGP', 'name' => 'Egyptian Pound'],
            ['code' => 'EUR', 'name' => 'Euro'],
            ['code' => 'GBP', 'name' => 'Pound Sterling'],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar'],
            ['code' => 'HRK', 'name' => 'Croatian Kuna'],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah'],
            ['code' => 'ILS', 'name' => 'Israeli New Shekel'],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar'],
            ['code' => 'JPY', 'name' => 'Japanese Yen'],
            ['code' => 'KES', 'name' => 'Kenyan Shilling'],
            ['code' => 'KRW', 'name' => 'South Korean Won'],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar'],
            ['code' => 'LKR', 'name' => 'Sri Lankan Rupee'],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham'],
            ['code' => 'MXN', 'name' => 'Mexican Peso'],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit'],
            ['code' => 'NGN', 'name' => 'Nigerian Naira'],
            ['code' => 'NOK', 'name' => 'Norwegian Krone'],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar'],
            ['code' => 'OMR', 'name' => 'Omani Rial'],
            ['code' => 'PEN', 'name' => 'Peruvian Sol'],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee'],
            ['code' => 'PLN', 'name' => 'Polish Złoty'],
            ['code' => 'RON', 'name' => 'Romanian Leu'],
            ['code' => 'RUB', 'name' => 'Russian Ruble'],
            ['code' => 'SEK', 'name' => 'Swedish Krona'],
            ['code' => 'SGD', 'name' => 'Singapore Dollar'],
            ['code' => 'THB', 'name' => 'Thai Baht'],
            ['code' => 'TRY', 'name' => 'Turkish Lira'],
            ['code' => 'TWD', 'name' => 'Taiwan Dollar'],
            ['code' => 'USD', 'name' => 'US Dollar'],
            ['code' => 'VND', 'name' => 'Vietnamese Dong'],
            ['code' => 'ZAR', 'name' => 'South African Rand'],
        ]);
    }
}

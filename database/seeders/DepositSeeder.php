<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('deposits')->insert([
            ['cb_id' => '2bbf394c-193b-5b2a-9155-3b4732659ede', 'account_id' => '2bbf394c-193b-5b2a-9155-3b4732659ede'],
        ]);
    }
}

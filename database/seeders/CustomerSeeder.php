<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            'unique_id' => '3fc9290f-9810-4f63-b4d3-98ce481a5278',
            'company_name' => 'Nexy Payments S.p.a.',
            'first_name' => 'Sara',
            'last_name' => 'Quirici',
            'email' => 'Sara.Quirici@nexigroup.com',
            'phone' => '+393497754898',
            'web_site' => 'https://www.nexi.it',
            'turnover_projection' => '<100k',
            'address' => 'Corso Sempione, 55',
            'zip_code' => '20149',
            'city' => 'Milano',
            'province' => 'MI',
            'country' => 'IT',
            'vat_number' => '10542790968',
            'fiscal_code' => '10542790968',
            'amazon_seller' => true,
            'amazon_vendor' => false,
            'amazon_advertising' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

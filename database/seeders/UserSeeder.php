<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Nexi Api User',
            'email' => 'nexi@quadran.ai',
            'password' => 'h!s13m9#NQyz',
        ]);
    }
}

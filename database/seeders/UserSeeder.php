<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create 2 users (one as user and one as manager)
        User::factory()->create([
            'name' => 'Test Manager',
            'email' => 'manager@mail.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);
        
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@mail.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}

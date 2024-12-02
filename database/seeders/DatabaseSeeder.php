<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::create([
            'name' => 'First Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
            'photo' => 'noimage.png',
        ]);

        User::create([
            'name' => 'First User',
            'email' => 'user@mail.com',
            'password' => Hash::make('rahasia123'),
            'role' => 'user',
            'photo' => 'noimage.png',
        ]);
    }
}

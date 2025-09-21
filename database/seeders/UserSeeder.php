<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create users with different roles
        $users = [
            [
                'nama' => 'Admin Direktur',
                'email' => 'direktur@kmp.com',
                'role' => 'direktur',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'John Marketing',
                'email' => 'marketing@kmp.com',
                'role' => 'marketing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Sari Purchasing',
                'email' => 'sari.purchasing@kmp.com',
                'role' => 'purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Ahmad Purchasing',
                'email' => 'ahmad.purchasing@kmp.com',
                'role' => 'purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Dewi Purchasing',
                'email' => 'dewi.purchasing@kmp.com',
                'role' => 'purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Budi Accounting',
                'email' => 'accounting@kmp.com',
                'role' => 'accounting',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}

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
                'username'=>'direktur123',
                'email' => 'direktur@kmp.com',
                'role' => 'direktur',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'John Marketing',
                'username'=>'marketing',
                'email' => 'marketing@kmp.com',
                'role' => 'marketing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Sari Purchasing',
                'username'=>'manager_purchasing',
                'email' => 'sari.purchasing@kmp.com',
                'role' => 'manager_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Ahmad Purchasing',
                'username'=>'staff_purchasing1',
                'email' => 'ahmad.purchasing@kmp.com',
                'role' => 'staff_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Dewi Purchasing',
                'username'=>'staff_purchasing2',
                'email' => 'dewi.purchasing@kmp.com',
                'role' => 'staff_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Budi Accounting',
                'username'=>'manager_accounting',
                'email' => 'accounting@kmp.com',
                'role' => 'manager_accounting',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']], // Check by username
                $userData // Update or create with this data
            );
        }
    }
}

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
            // Real Users from Company
            [
                'nama' => 'Mahenda Abdillah Kamil',
                'username' => 'mahenda',
                'email' => 'direktur@kmp.com',
                'role' => 'direktur',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Muhammad Romadhoni',
                'username' => 'dani',
                'email' => 'muhammadrhomadhoni95@gmail.com',
                'role' => 'manager_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Muhammad Zunaidi',
                'username' => 'juned',
                'email' => 'Muhammadzunaidi654@gmail.com',
                'role' => 'staff_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'M. Angga Fajar S,P',
                'username' => 'angga',
                'email' => 'Fajarangga0722@gmail.com',
                'role' => 'staff_purchasing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Dwi Putra Cakranegara',
                'username' => 'cakranegara',
                'email' => 'cakranegara2@gmail.com',
                'role' => 'marketing',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Dwi Amilatus Solicha',
                'username' => 'dwiamilatus',
                'email' => 'dwiamilatus386@gmail.com',
                'role' => 'manager_accounting',
                'password' => Hash::make('password123'),
                'status' => 'aktif'
            ],
            [
                'nama' => 'Annisa Sahira',
                'username' => 'annisashr',
                'email' => 'sahiraannisa15@gmail.com',
                'role' => 'staff_accounting',
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

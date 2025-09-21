<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PengelolaanAkunController extends Controller
{
    public function index()
    {
        // Mock data untuk tampilan (nanti akan diganti dengan data dari database)
        $allUsers = collect([
            (object)[
                'id' => 1,
                'nama' => 'John Doe',
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'role' => 'direktur',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(2)
            ],
            (object)[
                'id' => 2,
                'nama' => 'Jane Smith',
                'username' => 'janesmith',
                'email' => 'jane@example.com',
                'role' => 'marketing',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subDays(1)
            ],
            (object)[
                'id' => 3,
                'nama' => 'Bob Johnson',
                'username' => 'bobjohnson',
                'email' => 'bob@example.com',
                'role' => 'manager_purchasing',
                'foto_profil' => null,
                'status' => 'tidak_aktif',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(5)
            ],
            (object)[
                'id' => 4,
                'nama' => 'Alice Brown',
                'username' => 'alicebrown',
                'email' => 'alice@example.com',
                'role' => 'staff_purchasing',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(3)
            ],
            (object)[
                'id' => 5,
                'nama' => 'Charlie Wilson',
                'username' => 'charliewilson',
                'email' => 'charlie@example.com',
                'role' => 'staff_accounting',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(1)
            ],
            (object)[
                'id' => 6,
                'nama' => 'Diana Prince',
                'username' => 'dianaprince',
                'email' => 'diana@example.com',
                'role' => 'manager_accounting',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subHours(12)
            ],
            (object)[
                'id' => 7,
                'nama' => 'Edward Norton',
                'username' => 'enorton',
                'email' => 'edward@example.com',
                'role' => 'staff_purchasing',
                'foto_profil' => null,
                'status' => 'tidak_aktif',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(6)
            ],
            (object)[
                'id' => 8,
                'nama' => 'Fiona Green',
                'username' => 'fgreen',
                'email' => 'fiona@example.com',
                'role' => 'marketing',
                'foto_profil' => null,
                'status' => 'aktif',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subHours(8)
            ]
        ]);

        // Simulate pagination
        $perPage = 5;
        $currentPage = request('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $users = $allUsers->slice($offset, $perPage)->values();
        
        // Create a simple pagination object
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $users,
            $allUsers->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('pages.pengelolaan-akun', compact('users'));
    }
}

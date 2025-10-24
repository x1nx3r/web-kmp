<?php

namespace App\Http\Controllers\Direktur;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PengelolaanAkunController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Sorting
        $sort = $request->get('sort', 'nama_asc');
        switch ($sort) {
            case 'nama_desc':
                $query->orderBy('nama', 'desc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'updated_desc':
                $query->orderBy('updated_at', 'desc');
                break;
            default:
                $query->orderBy('nama', 'asc');
                break;
        }
        
        // Pagination
        $users = $query->paginate(5)->withQueryString();

        return view('pages.pengelolaan-akun', compact('users'));
    }
    
    public function show(User $user)
    {
        try {
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'user' => $user
                ]);
            }
            
            return view('pages.pengelolaan-akun-detail', compact('user'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            return redirect()->route('pengelolaan-akun.index')->with('error', 'User tidak ditemukan');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:direktur,marketing,manager_purchasing,staff_purchasing,manager_accounting,staff_accounting',
            'status' => 'required|in:aktif,tidak_aktif',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nama.required' => 'Nama harus diisi',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah digunakan',
            'email.email' => 'Format email tidak valid',
            'role.required' => 'Role harus dipilih',
            'role.in' => 'Role tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validasi gagal'
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'password' => Hash::make($request->password),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'message' => 'User berhasil dibuat'
                ]);
            }

            return redirect()->route('pengelolaan-akun.index')->with('success', 'User berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data')->withInput();
        }
    }

    public function edit(User $user)
    {
        try {
            \Log::info('Edit request received for user:', ['user_id' => $user->id, 'is_ajax' => request()->ajax()]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'user' => $user
                ]);
            }
            
            return view('pages.pengelolaan-akun-edit', compact('user'));
        } catch (\Exception $e) {
            \Log::error('Error in edit method:', ['error' => $e->getMessage()]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            return redirect()->route('pengelolaan-akun.index')->with('error', 'User tidak ditemukan');
        }
    }

    public function update(Request $request, User $user)
    {
        Log::info('Update request received', [
            'user_id' => $user->id,
            'method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'data' => $request->all()
        ]);
        
        try {
            $rules = [
                'nama' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|in:direktur,marketing,manager_purchasing,staff_purchasing,manager_accounting,staff_accounting',
                'status' => 'required|in:aktif,tidak_aktif',
            ];

            // Only validate password if provided
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:6|confirmed';
            }

            Log::info('Before validation', ['rules' => $rules, 'data' => $request->all()]);

            $validator = Validator::make($request->all(), $rules, [
                'nama.required' => 'Nama harus diisi',
                'username.required' => 'Username harus diisi',
                'username.unique' => 'Username sudah digunakan',
                'email.required' => 'Email harus diisi',
                'email.unique' => 'Email sudah digunakan',
                'email.email' => 'Format email tidak valid',
                'role.required' => 'Role harus dipilih',
                'role.in' => 'Role tidak valid',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            if ($validator->fails()) {
                Log::info('Validation failed', ['errors' => $validator->errors()]);
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors(),
                        'message' => 'Validasi gagal'
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Log::info('Validation passed, preparing update data');

            $updateData = [
                'nama' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
                Log::info('Password will be updated');
            }

            Log::info('About to update user', ['user_id' => $user->id, 'update_data' => $updateData]);

            $user->update($updateData);

            Log::info('User updated successfully', ['user' => $user->fresh()]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'user' => $user->fresh(),
                    'message' => 'User berhasil diupdate'
                ]);
            }

            return redirect()->route('pengelolaan-akun.index')->with('success', 'User berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau terjadi kesalahan'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'User tidak ditemukan atau terjadi kesalahan')->withInput();
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dihapus'
                ]);
            }

            return redirect()->route('pengelolaan-akun.index')->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau tidak dapat dihapus'
                ], 500);
            }
            
            return redirect()->route('pengelolaan-akun.index')->with('error', 'User tidak ditemukan atau tidak dapat dihapus');
        }
    }
}


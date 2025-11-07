<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;

class PengaturanController extends Controller
{
    /**
     * Show the settings page
     */
    public function index()
    {
        $user = Auth::user();
        return view('pages.pengaturan', compact('user'));
    }

    /**
     * Update user settings
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password' => 'required_with:password',
            'password' => 'nullable|min:8|confirmed',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        // Verify current password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }
        }

        // Prepare data for update
        $updateData = [
            'nama' => $request->nama,
            'username' => $request->username,
        ];

        // Handle profile photo upload
        if ($request->hasFile('foto_profil')) {
            // Delete old photo if exists
            if ($user->foto_profil && Storage::disk('public')->exists('profile-photos/' . $user->foto_profil)) {
                Storage::disk('public')->delete('profile-photos/' . $user->foto_profil);
            }

            // Store new photo with original name + timestamp
            $file = $request->file('foto_profil');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profile-photos', $fileName, 'public');
            $updateData['foto_profil'] = $fileName; // Only save filename, not path
        }

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Update user
        User::where('id', $user->id)->update($updateData);

        return redirect()->route('pengaturan')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}

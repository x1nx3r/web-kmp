<?php

namespace App\Http\Controllers\Marketing;

use App\Models\Klien;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KlienController extends Controller
{
    public function index(Request $request)
    {
        $query = Klien::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by status (jika diperlukan nanti)
        if ($request->has('status') && $request->status != '') {
            // Bisa ditambahkan filter status nanti
        }

        $kliens = $query->orderBy('nama', 'asc')->paginate(10);

        return view('pages.marketing.daftar-klien', compact('kliens'));
    }

    // Method untuk store, update, delete bisa ditambahkan nanti
    public function store(Request $request)
    {
        // Akan diimplementasi nanti
    }

    public function show(Klien $klien)
    {
        // Akan diimplementasi nanti
    }

    public function update(Request $request, Klien $klien)
    {
        // Akan diimplementasi nanti
    }

    public function destroy(Klien $klien)
    {
        // Akan diimplementasi nanti
    }
}
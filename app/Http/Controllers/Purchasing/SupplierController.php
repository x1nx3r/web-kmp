<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Filter by status (jika diperlukan nanti)
        if ($request->has('status') && $request->status != '') {
            // Bisa ditambahkan filter status nanti
        }

        $suppliers = $query->orderBy('nama', 'asc')->paginate(10);

        return view('Pages.purchasing.supplier', compact('suppliers'));
    }

    // Method untuk store, update, delete bisa ditambahkan nanti
    public function store(Request $request)
    {
        // Akan diimplementasi nanti
    }

    public function show(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }

    public function destroy(Supplier $supplier)
    {
        // Akan diimplementasi nanti
    }
}

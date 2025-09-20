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

    

        $suppliers = $query->orderBy('nama', 'asc')->paginate(5);

        return view('pages.purchasing.supplier', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.purchasing.supplier.tambah');
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

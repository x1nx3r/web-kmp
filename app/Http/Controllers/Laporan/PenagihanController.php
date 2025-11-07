<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenagihanController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Penagihan';
        $activeTab = 'penagihan';
        
        
        return view('pages.laporan.penagihan', compact(
            'title', 
            'activeTab', 
        ));
    }
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}

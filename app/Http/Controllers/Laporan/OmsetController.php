<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OmsetController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Omset';
        $activeTab = 'omset';
        return view('pages.laporan.omset', compact(
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

<?php

namespace App\Console\Commands;

use App\Models\TargetOmset;
use App\Models\TargetOmsetSnapshot;
use App\Models\InvoicePenagihan;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaveTargetOmsetSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'target:snapshot 
                            {type=all : Type of snapshot (weekly, monthly, yearly, all)}
                            {--year= : Specific year to snapshot (default: current year)}';

    /**
     * The console command description.
     */
    protected $description = 'Save snapshot of target omset progress';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $year = $this->option('year') ?? Carbon::now()->year;
        
        $this->info("📊 Saving Target Omset Snapshot for {$year}...");
        
        // Get target for the year
        $targetOmset = TargetOmset::getTargetForYear($year);
        
        if (!$targetOmset) {
            $this->error("❌ No target found for year {$year}");
            return 1;
        }
        
        $saved = 0;
        
        if ($type === 'weekly' || $type === 'all') {
            $saved += $this->saveWeeklySnapshot($targetOmset, $year);
        }
        
        if ($type === 'monthly' || $type === 'all') {
            $saved += $this->saveMonthlySnapshots($targetOmset, $year);
        }
        
        if ($type === 'yearly' || $type === 'all') {
            $saved += $this->saveYearlySnapshot($targetOmset, $year);
        }
        
        $this->info("✅ Successfully saved {$saved} snapshot(s)!");
        return 0;
    }
    
    /**
     * Save weekly snapshot
     */
    private function saveWeeklySnapshot($targetOmset, $year)
    {
        $this->info("  📅 Saving weekly snapshot...");
        
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weekNumber = Carbon::now()->week;
        $currentMonth = Carbon::now()->month;
        
        // Calculate actual omset for current week
        $actualOmset = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereBetween('pengiriman.updated_at', [$startOfWeek, $endOfWeek])
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        $targetOmset->saveSnapshot(
            $actualOmset,
            'weekly',
            $currentMonth,
            $weekNumber,
            'System'
        );
        
        $this->line("    Week {$weekNumber}: Rp " . number_format($actualOmset, 0, ',', '.'));
        
        return 1;
    }
    
    /**
     * Save monthly snapshots
     */
    private function saveMonthlySnapshots($targetOmset, $year)
    {
        $this->info("  📆 Saving monthly snapshots...");
        
        $saved = 0;
        $currentMonth = Carbon::now()->month;
        
        // Save snapshots for all months up to current month
        for ($bulan = 1; $bulan <= $currentMonth; $bulan++) {
            $actualOmset = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
                ->where('pengiriman.status', 'berhasil')
                ->whereYear('pengiriman.updated_at', $year)
                ->whereMonth('pengiriman.updated_at', $bulan)
                ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
            
            $targetOmset->saveSnapshot(
                $actualOmset,
                'monthly',
                $bulan,
                null,
                'System'
            );
            
            $monthName = Carbon::create($year, $bulan, 1)->format('F');
            $this->line("    {$monthName}: Rp " . number_format($actualOmset, 0, ',', '.'));
            
            $saved++;
        }
        
        return $saved;
    }
    
    /**
     * Save yearly snapshot
     */
    private function saveYearlySnapshot($targetOmset, $year)
    {
        $this->info("  📊 Saving yearly snapshot...");
        
        $actualOmset = InvoicePenagihan::join('pengiriman', 'invoice_penagihan.pengiriman_id', '=', 'pengiriman.id')
            ->where('pengiriman.status', 'berhasil')
            ->whereYear('pengiriman.updated_at', $year)
            ->sum('invoice_penagihan.amount_after_refraksi') ?? 0;
        
        $targetOmset->saveSnapshot(
            $actualOmset,
            'yearly',
            null,
            null,
            'System'
        );
        
        $this->line("    Year {$year}: Rp " . number_format($actualOmset, 0, ',', '.'));
        
        return 1;
    }
}

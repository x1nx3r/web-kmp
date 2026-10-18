<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\Spesifikasi;
use App\Models\BahanBakuKlien;
use App\Models\Klien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class SpesifikasiExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->travelTo(Carbon::parse('2026-01-01 10:00:00'));
    }

    /** @test */
    public function it_exports_all_materials_when_no_filter_is_applied()
    {
        $klien = Klien::factory()->create(['nama' => 'PT Contoh', 'cabang' => 'Jakarta']);

        BahanBakuKlien::factory()->create([
            'klien_id' => $klien->id,
            'nama' => 'Steel Plate',
            'status' => 'aktif',
        ]);
        BahanBakuKlien::factory()->create([
            'klien_id' => $klien->id,
            'nama' => 'Wood Plank',
            'status' => 'pending',
        ]);

        Excel::fake();

        Livewire::test(Spesifikasi::class)->call('exportExcel');

        Excel::assertDownloaded(
            'Spesifikasi_Material_20260101_100000.xlsx',
            fn ($export) => count($export->array()) === 7
        );
    }

    /** @test */
    public function it_exports_only_materials_matching_the_active_filter()
    {
        $klien = Klien::factory()->create(['nama' => 'PT Contoh', 'cabang' => 'Jakarta']);

        BahanBakuKlien::factory()->create([
            'klien_id' => $klien->id,
            'nama' => 'Steel Plate',
            'status' => 'aktif',
        ]);
        BahanBakuKlien::factory()->create([
            'klien_id' => $klien->id,
            'nama' => 'Wood Plank',
            'status' => 'aktif',
        ]);

        Excel::fake();

        Livewire::test(Spesifikasi::class)
            ->set('materialSearch', 'Steel')
            ->call('exportExcel');

        Excel::assertDownloaded(
            'Spesifikasi_Material_20260101_100000.xlsx',
            function ($export) {
                $rows = $export->array();

                $names = array_column(array_slice($rows, 6), 1);

                return $names === ['Steel Plate'];
            }
        );
    }
}

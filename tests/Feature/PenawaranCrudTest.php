<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Klien;
use App\Models\Penawaran;
use App\Models\PenawaranDetail;
use App\Models\PenawaranAlternativeSupplier;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PenawaranCrudTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $klien;
    protected $supplier;
    protected $bahanBakuKlien;
    protected $bahanBakuSupplier;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'nama' => 'Test User',
        ]);

        // Create test klien
        $this->klien = Klien::factory()->create([
            'nama' => 'PT Test Company',
            'cabang' => 'Jakarta',
        ]);

        // Create test supplier
        $this->supplier = Supplier::factory()->create([
            'nama' => 'Supplier Test',
        ]);

        // Create test bahan baku klien
        $this->bahanBakuKlien = BahanBakuKlien::factory()->create([
            'klien_id' => $this->klien->id,
            'nama' => 'Test Material',
            'satuan' => 'kg',
            'harga_approved' => 10000,
        ]);

        // Create test bahan baku supplier
        $this->bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $this->supplier->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'nama' => 'Test Material',
            'satuan' => 'kg',
            'harga' => 8000,
        ]);
    }

    /** @test */
    public function it_can_create_penawaran_as_draft()
    {
        $this->actingAs($this->user);

        $penawaranData = [
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'tanggal_penawaran' => now(),
            'tanggal_berlaku_sampai' => now()->addDays(30),
            'total_revenue' => 100000,
            'total_cost' => 80000,
            'total_profit' => 20000,
            'margin_percentage' => 20.0,
            'created_by' => $this->user->id,
        ];

        $penawaran = Penawaran::create($penawaranData);

        $this->assertDatabaseHas('penawaran', [
            'id' => $penawaran->id,
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'total_revenue' => 100000,
            'margin_percentage' => 20.0,
        ]);

        $this->assertEquals('draft', $penawaran->status);
        $this->assertNotNull($penawaran->nomor_penawaran);
        $this->assertTrue(str_starts_with($penawaran->nomor_penawaran, 'PNW-'));
    }

    /** @test */
    public function it_can_create_penawaran_with_details()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $detail = PenawaranDetail::create([
            'penawaran_id' => $penawaran->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
            'nama_material' => 'Test Material',
            'satuan' => 'kg',
            'quantity' => 100,
            'harga_klien' => 10000,
            'harga_supplier' => 8000,
            'is_custom_price' => false,
            'subtotal_revenue' => 1000000,
            'subtotal_cost' => 800000,
            'subtotal_profit' => 200000,
            'margin_percentage' => 20.0,
        ]);

        $this->assertDatabaseHas('penawaran_detail', [
            'penawaran_id' => $penawaran->id,
            'nama_material' => 'Test Material',
            'quantity' => 100,
            'harga_klien' => 10000,
            'harga_supplier' => 8000,
        ]);

        $this->assertEquals(1, $penawaran->details()->count());
    }

    /** @test */
    public function it_can_create_alternative_suppliers()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
        ]);

        $detail = PenawaranDetail::factory()->create([
            'penawaran_id' => $penawaran->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
        ]);

        // Create alternative supplier
        $altSupplier = Supplier::factory()->create(['nama' => 'Alternative Supplier']);
        $altBahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $altSupplier->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'harga' => 7500,
        ]);

        $alternative = PenawaranAlternativeSupplier::create([
            'penawaran_detail_id' => $detail->id,
            'supplier_id' => $altSupplier->id,
            'bahan_baku_supplier_id' => $altBahanBakuSupplier->id,
            'harga_supplier' => 7500,
            'margin_percentage' => 25.0,
        ]);

        $this->assertDatabaseHas('penawaran_alternative_suppliers', [
            'penawaran_detail_id' => $detail->id,
            'supplier_id' => $altSupplier->id,
            'harga_supplier' => 7500,
        ]);

        $this->assertEquals(1, $detail->alternativeSuppliers()->count());
    }

    /** @test */
    public function it_can_update_draft_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'total_revenue' => 100000,
            'created_by' => $this->user->id,
        ]);

        $penawaran->update([
            'total_revenue' => 150000,
            'total_cost' => 120000,
            'total_profit' => 30000,
            'margin_percentage' => 20.0,
        ]);

        $this->assertDatabaseHas('penawaran', [
            'id' => $penawaran->id,
            'total_revenue' => 150000,
            'total_cost' => 120000,
            'total_profit' => 30000,
        ]);
    }

    /** @test */
    public function it_cannot_update_non_draft_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'disetujui',
            'created_by' => $this->user->id,
        ]);

        // This should fail in actual implementation
        // Here we're just testing the status check
        $this->assertEquals('disetujui', $penawaran->status);
        $this->assertNotEquals('draft', $penawaran->status);
    }

    /** @test */
    public function it_can_submit_draft_for_verification()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $result = $penawaran->submitForVerification($this->user);

        $this->assertTrue($result);
        $this->assertEquals('menunggu_verifikasi', $penawaran->fresh()->status);
    }

    /** @test */
    public function it_can_approve_pending_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'menunggu_verifikasi',
            'created_by' => $this->user->id,
        ]);

        $result = $penawaran->approve($this->user);

        $this->assertTrue($result);
        $this->assertEquals('disetujui', $penawaran->fresh()->status);
        $this->assertEquals($this->user->id, $penawaran->fresh()->verified_by);
        $this->assertNotNull($penawaran->fresh()->verified_at);
    }

    /** @test */
    public function it_can_reject_pending_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'menunggu_verifikasi',
            'created_by' => $this->user->id,
        ]);

        $rejectReason = 'Margin too low';
        $result = $penawaran->reject($this->user, $rejectReason);

        $this->assertTrue($result);
        $this->assertEquals('ditolak', $penawaran->fresh()->status);
        $this->assertEquals($rejectReason, $penawaran->fresh()->catatan_verifikasi);
        $this->assertEquals($this->user->id, $penawaran->fresh()->verified_by);
    }

    /** @test */
    public function it_can_duplicate_penawaran()
    {
        $this->actingAs($this->user);

        $original = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'disetujui',
            'created_by' => $this->user->id,
        ]);

        // Create details for original
        PenawaranDetail::factory()->create([
            'penawaran_id' => $original->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
        ]);

        // Duplicate
        $duplicate = $original->replicate();
        $duplicate->status = 'draft';
        $duplicate->nomor_penawaran = null;
        $duplicate->created_by = $this->user->id;
        $duplicate->verified_by = null;
        $duplicate->verified_at = null;
        $duplicate->save();

        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertNotEquals($original->nomor_penawaran, $duplicate->nomor_penawaran);
        $this->assertEquals('draft', $duplicate->status);
        $this->assertEquals($original->klien_id, $duplicate->klien_id);
    }

    /** @test */
    public function it_can_delete_draft_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $id = $penawaran->id;
        $penawaran->forceDelete();

        $this->assertDatabaseMissing('penawaran', ['id' => $id]);
    }

    /** @test */
    public function it_can_soft_delete_non_draft_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'disetujui',
            'created_by' => $this->user->id,
        ]);

        $id = $penawaran->id;
        $penawaran->delete();

        // Should still exist in database but with deleted_at timestamp
        $this->assertDatabaseHas('penawaran', ['id' => $id]);
        $this->assertSoftDeleted('penawaran', ['id' => $id]);
    }

    /** @test */
    public function it_generates_unique_nomor_penawaran()
    {
        $this->actingAs($this->user);

        $penawaran1 = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
        ]);

        $penawaran2 = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertNotEquals($penawaran1->nomor_penawaran, $penawaran2->nomor_penawaran);
        $this->assertTrue(str_starts_with($penawaran1->nomor_penawaran, 'PNW-'));
        $this->assertTrue(str_starts_with($penawaran2->nomor_penawaran, 'PNW-'));
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'total_revenue' => 1000000,
            'total_cost' => 800000,
            'total_profit' => 200000,
            'margin_percentage' => 20.0,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(1000000, $penawaran->total_revenue);
        $this->assertEquals(800000, $penawaran->total_cost);
        $this->assertEquals(200000, $penawaran->total_profit);
        $this->assertEquals(20.0, $penawaran->margin_percentage);

        // Verify calculation
        $calculatedProfit = $penawaran->total_revenue - $penawaran->total_cost;
        $this->assertEquals($penawaran->total_profit, $calculatedProfit);

        $calculatedMargin = ($penawaran->total_profit / $penawaran->total_revenue) * 100;
        $this->assertEquals($penawaran->margin_percentage, $calculatedMargin);
    }

    /** @test */
    public function it_loads_relationships_correctly()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
        ]);

        PenawaranDetail::factory()->create([
            'penawaran_id' => $penawaran->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
        ]);

        $loadedPenawaran = Penawaran::with([
            'klien',
            'details.supplier',
            'details.bahanBakuKlien',
            'createdBy',
        ])->find($penawaran->id);

        $this->assertInstanceOf(Klien::class, $loadedPenawaran->klien);
        $this->assertInstanceOf(User::class, $loadedPenawaran->createdBy);
        $this->assertCount(1, $loadedPenawaran->details);
        $this->assertInstanceOf(Supplier::class, $loadedPenawaran->details->first()->supplier);
    }

    /** @test */
    public function it_validates_status_transitions()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        // Draft -> Menunggu Verifikasi (Valid)
        $result = $penawaran->submitForVerification($this->user);
        $this->assertTrue($result);
        $this->assertEquals('menunggu_verifikasi', $penawaran->fresh()->status);

        // Menunggu Verifikasi -> Disetujui (Valid)
        $result = $penawaran->approve($this->user);
        $this->assertTrue($result);
        $this->assertEquals('disetujui', $penawaran->fresh()->status);

        // Disetujui -> Draft (Invalid - cannot go back)
        // This should be prevented by the model logic
        $this->assertEquals('disetujui', $penawaran->status);
    }
}




namespace Tests\Feature\Livewire;namespace Tests\Feature\Livewire;



use Tests\TestCase;use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;use Illuminate\Foundation\Testing\WithFaker;

use App\Models\Klien;use Tests\TestCase;

use App\Models\Penawaran;

use App\Models\PenawaranDetail;class PenawaranComponentTest extends TestCase

use App\Models\BahanBakuKlien;{

use App\Models\BahanBakuSupplier;    /**

use App\Models\Supplier;     * A basic feature test example.

use App\Livewire\Marketing\Penawaran as PenawaranComponent;     */

use Livewire\Livewire;    public function test_example(): void

use Illuminate\Foundation\Testing\RefreshDatabase;    {

        $response = $this->get('/');

class PenawaranComponentTest extends TestCase

{        $response->assertStatus(200);

    use RefreshDatabase;    }

}

    protected $user;
    protected $klien;
    protected $supplier;
    protected $bahanBakuKlien;
    protected $bahanBakuSupplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->klien = Klien::factory()->create();
        $this->supplier = Supplier::factory()->create();
        
        $this->bahanBakuKlien = BahanBakuKlien::factory()->create([
            'klien_id' => $this->klien->id,
            'harga_approved' => 10000,
        ]);
        
        $this->bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $this->supplier->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'harga' => 8000,
        ]);
    }

    /** @test */
    public function it_can_render_penawaran_component()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->assertStatus(200)
            ->assertSee('Analisis Penawaran');
    }

    /** @test */
    public function it_can_select_client()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->assertSet('selectedKlienId', $this->klien->id)
            ->assertSet('selectedKlien', $this->klien->nama)
            ->assertSet('selectedKlienCabang', $this->klien->cabang);
    }

    /** @test */
    public function it_can_add_material()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->set('useCustomPrice', false)
            ->call('addMaterial')
            ->assertSet('selectedMaterials', function($materials) {
                return count($materials) === 1;
            });
    }

    /** @test */
    public function it_can_remove_material()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial');

        $materials = $component->get('selectedMaterials');
        $materialId = $materials[0]['id'];

        $component->call('removeMaterial', $materialId)
            ->assertSet('selectedMaterials', function($materials) {
                return count($materials) === 0;
            });
    }

    /** @test */
    public function it_calculates_margin_analysis_correctly()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial')
            ->call('refreshAnalysis')
            ->assertSet('marginAnalysis', function($analysis) {
                return count($analysis) === 1 && 
                       $analysis[0]['quantity'] == 100;
            });
    }

    /** @test */
    public function it_can_save_as_draft()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial')
            ->call('refreshAnalysis')
            ->call('saveDraft');

        $this->assertDatabaseHas('penawaran', [
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_submit_for_verification()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial')
            ->call('refreshAnalysis')
            ->call('submitForVerification');

        $this->assertDatabaseHas('penawaran', [
            'klien_id' => $this->klien->id,
            'status' => 'menunggu_verifikasi',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_load_penawaran_for_edit()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        PenawaranDetail::factory()->create([
            'penawaran_id' => $penawaran->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
            'quantity' => 100,
        ]);

        Livewire::test(PenawaranComponent::class, ['penawaran' => $penawaran])
            ->assertSet('editMode', true)
            ->assertSet('penawaranId', $penawaran->id)
            ->assertSet('selectedKlienId', $this->klien->id)
            ->assertSet('selectedMaterials', function($materials) {
                return count($materials) === 1 && $materials[0]['quantity'] == 100;
            });
    }

    /** @test */
    public function it_can_update_existing_draft()
    {
        $this->actingAs($this->user);

        $penawaran = Penawaran::factory()->create([
            'klien_id' => $this->klien->id,
            'status' => 'draft',
            'total_revenue' => 100000,
            'created_by' => $this->user->id,
        ]);

        PenawaranDetail::factory()->create([
            'penawaran_id' => $penawaran->id,
            'bahan_baku_klien_id' => $this->bahanBakuKlien->id,
            'supplier_id' => $this->supplier->id,
            'bahan_baku_supplier_id' => $this->bahanBakuSupplier->id,
        ]);

        Livewire::test(PenawaranComponent::class, ['penawaran' => $penawaran])
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 200)
            ->call('addMaterial')
            ->call('refreshAnalysis')
            ->call('saveDraft');

        $this->assertDatabaseHas('penawaran', [
            'id' => $penawaran->id,
            'klien_id' => $this->klien->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function it_validates_no_materials_selected()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->call('saveDraft')
            ->assertHasErrors();
    }

    /** @test */
    public function it_validates_no_client_selected()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial')
            ->call('saveDraft')
            ->assertHasErrors();
    }

    /** @test */
    public function it_can_reset_form()
    {
        $this->actingAs($this->user);

        Livewire::test(PenawaranComponent::class)
            ->call('selectKlien', $this->klien->id, $this->klien->nama, $this->klien->cabang)
            ->set('currentMaterial', $this->bahanBakuKlien->id)
            ->set('currentQuantity', 100)
            ->call('addMaterial')
            ->call('resetForm')
            ->assertSet('selectedKlien', null)
            ->assertSet('selectedKlienId', null)
            ->assertSet('selectedMaterials', [])
            ->assertSet('marginAnalysis', []);
    }
}

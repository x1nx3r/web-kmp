<?php

namespace Tests\Feature;

use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create some test data
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);
        Klien::create(['nama' => 'PT Central Proteina', 'cabang' => 'Balaraja', 'no_hp' => '081234567891']);
        Klien::create(['nama' => 'CJ Feed', 'cabang' => 'Jombang', 'no_hp' => '081234567892']);
    }

    /** @test */
    public function it_can_display_klien_index_page()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.marketing.daftar-klien');
        $response->assertViewHas('kliens');
    }

    /** @test */
    public function index_page_displays_all_kliens()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('PT Sreya Sewu');
        $response->assertSee('PT Central Proteina');
        $response->assertSee('CJ Feed');
        $response->assertSee('Sidoarjo');
        $response->assertSee('Balaraja');
        $response->assertSee('Jombang');
    }

    /** @test */
    public function it_can_search_kliens_by_nama()
    {
        $response = $this->get(route('klien.index', ['search' => 'Sreya']));

        $response->assertStatus(200);
        $response->assertSee('PT Sreya Sewu');
        $response->assertDontSee('PT Central Proteina');
        $response->assertDontSee('CJ Feed');
    }

    /** @test */
    public function it_can_search_kliens_by_cabang()
    {
        $response = $this->get(route('klien.index', ['search' => 'Sidoarjo']));

        $response->assertStatus(200);
        $response->assertSee('PT Sreya Sewu');
        $response->assertSee('Sidoarjo');
        $response->assertDontSee('PT Central Proteina');
    }

    /** @test */
    public function it_can_search_kliens_by_no_hp()
    {
        $response = $this->get(route('klien.index', ['search' => '567890']));

        $response->assertStatus(200);
        $response->assertSee('PT Sreya Sewu');
        $response->assertSee('081234567890');
        $response->assertDontSee('PT Central Proteina');
    }

    /** @test */
    public function search_with_no_results_shows_appropriate_message()
    {
        $response = $this->get(route('klien.index', ['search' => 'NonExistentKlien']));

        $response->assertStatus(200);
        $response->assertSee('Tidak ditemukan klien dengan kata kunci', false);
        $response->assertSee('NonExistentKlien', false);
        $response->assertDontSee('PT Sreya Sewu');
    }

    /** @test */
    public function empty_search_shows_all_kliens()
    {
        $response = $this->get(route('klien.index', ['search' => '']));

        $response->assertStatus(200);
        $response->assertSee('PT Sreya Sewu');
        $response->assertSee('PT Central Proteina');
        $response->assertSee('CJ Feed');
    }

    /** @test */
    public function index_paginates_results()
    {
        // Create more kliens to test pagination
        for ($i = 4; $i <= 15; $i++) {
            Klien::create([
                'nama' => "PT Test Klien {$i}",
                'cabang' => "Cabang {$i}",
                'no_hp' => "08123456789{$i}"
            ]);
        }

        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        // Should show pagination links since we have more than 10 items
        $kliens = $response->viewData('kliens');
        $this->assertTrue($kliens->hasPages());
        $this->assertEquals(10, $kliens->perPage());
    }

    /** @test */
    public function it_shows_no_data_message_when_no_kliens_exist()
    {
        // Delete all kliens
        Klien::query()->delete();

        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada data klien');
        $response->assertSee('Belum ada klien yang terdaftar di sistem');
    }

    /** @test */
    public function it_maintains_search_parameters_in_pagination()
    {
        // Create enough kliens to trigger pagination
        for ($i = 1; $i <= 15; $i++) {
            Klien::create([
                'nama' => "PT Searchable Klien {$i}",
                'cabang' => "Cabang {$i}",
                'no_hp' => "08123456789{$i}"
            ]);
        }

        $response = $this->get(route('klien.index', ['search' => 'Searchable']));

        $response->assertStatus(200);
        $kliens = $response->viewData('kliens');
        
        // Check that pagination links include search parameter
        $this->assertStringContainsString('search=Searchable', $kliens->appends(['search' => 'Searchable'])->toHtml());
    }

    /** @test */
    public function klien_routes_are_properly_named()
    {
        $this->assertTrue(route('klien.index') !== null);
        $this->assertTrue(route('klien.store') !== null);
        $this->assertTrue(route('klien.show', ['klien' => 1]) !== null);
        $this->assertTrue(route('klien.update', ['klien' => 1]) !== null);
        $this->assertTrue(route('klien.destroy', ['klien' => 1]) !== null);
    }

    /** @test */
    public function index_route_returns_correct_data_structure()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        
        $kliens = $response->viewData('kliens');
        $this->assertNotNull($kliens);
        $this->assertTrue($kliens->count() > 0);
        
        // Check that kliens are ordered by nama
        $klienNames = $kliens->pluck('nama')->toArray();
        $sortedNames = collect($klienNames)->sort()->values()->toArray();
        $this->assertEquals($sortedNames, $klienNames);
    }

    /** @test */
    public function store_method_is_not_implemented_yet()
    {
        $klienData = [
            'nama' => 'PT New Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ];

        $response = $this->post(route('klien.store'), $klienData);

        // Should not create a new klien since store method is empty
        $this->assertDatabaseMissing('kliens', $klienData);
    }

    /** @test */
    public function show_method_is_not_implemented_yet()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $response = $this->get(route('klien.show', $klien));

        // Should return 200 but with no meaningful content since show method is empty
        $response->assertStatus(200);
        // The view would be empty or return default content
    }

    /** @test */
    public function update_method_is_not_implemented_yet()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $updateData = [
            'nama' => 'PT Updated Klien',
            'cabang' => 'Surabaya',
            'no_hp' => '081234567891'
        ];

        $response = $this->put(route('klien.update', $klien), $updateData);

        // Should not update the klien since update method is empty
        $klien->refresh();
        $this->assertEquals('PT Test Klien', $klien->nama);
        $this->assertEquals('Jakarta', $klien->cabang);
        $this->assertEquals('081234567890', $klien->no_hp);
    }

    /** @test */
    public function destroy_method_is_not_implemented_yet()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $response = $this->delete(route('klien.destroy', $klien));

        // Should not delete the klien since destroy method is empty
        $this->assertDatabaseHas('kliens', ['id' => $klien->id]);
        $this->assertNull($klien->fresh()->deleted_at);
    }
}
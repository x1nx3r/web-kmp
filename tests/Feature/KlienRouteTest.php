<?php

namespace Tests\Feature;

use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienRouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function klien_index_route_exists_and_is_accessible()
    {
        $response = $this->get('/klien');

        $response->assertStatus(200);
    }

    /** @test */
    public function klien_index_route_has_correct_name()
    {
        $this->assertStringEndsWith('/klien', route('klien.index'));
    }

    /** @test */
    public function klien_store_route_exists()
    {
        $response = $this->post('/klien', []);

        // Should not be 404 (route exists), but might be 405 or 422 depending on implementation
        $response->assertStatus(200); // Will be updated when store method is implemented
    }

    /** @test */
    public function klien_show_route_exists_with_parameter()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $response = $this->get("/klien/{$klien->id}");

        $response->assertStatus(200); // Will be updated when show method is implemented
    }

    /** @test */
    public function klien_update_route_exists_with_put_method()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $response = $this->put("/klien/{$klien->id}", []);

        $response->assertStatus(200); // Will be updated when update method is implemented
    }

    /** @test */
    public function klien_destroy_route_exists_with_delete_method()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $response = $this->delete("/klien/{$klien->id}");

        $response->assertStatus(200); // Will be updated when destroy method is implemented
    }

    /** @test */
    public function klien_routes_follow_restful_naming_convention()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $this->assertStringEndsWith('/klien', route('klien.index'));
        $this->assertStringEndsWith('/klien', route('klien.store'));
        $this->assertStringEndsWith("/klien/{$klien->id}", route('klien.show', $klien));
        $this->assertStringEndsWith("/klien/{$klien->id}", route('klien.update', $klien));
        $this->assertStringEndsWith("/klien/{$klien->id}", route('klien.destroy', $klien));
    }

    /** @test */
    public function klien_index_view_renders_correctly()
    {
        Klien::create(['nama' => 'PT Test Klien', 'cabang' => 'Jakarta', 'no_hp' => '081234567890']);

        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.marketing.daftar-klien');
        $response->assertSee('Daftar Klien');
        $response->assertSee('Kelola data klien perusahaan');
        $response->assertSee('PT Test Klien');
        $response->assertSee('Jakarta');
    }

    /** @test */
    public function klien_index_view_has_correct_page_title()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('<title>Daftar Klien - Kamil Maju Persada</title>', false);
    }

    /** @test */
    public function klien_index_view_displays_search_form()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('name="search"', false);
        $response->assertSee('Cari nama klien, cabang, atau no HP...', false);
        $response->assertSee('name="status"', false);
    }

    /** @test */
    public function klien_index_view_displays_table_headers()
    {
        // Create some klien data so table is shown
        Klien::create(['nama' => 'PT Test', 'cabang' => 'Jakarta', 'no_hp' => '081234567890']);
        
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('Nama Klien', false);
        $response->assertSee('Cabang', false);
        $response->assertSee('No HP', false);
        $response->assertSee('Tanggal Diubah', false);
        $response->assertSee('Aksi', false);
    }

    /** @test */
    public function klien_index_view_displays_add_button()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Klien');
        $response->assertSee('fa-plus', false);
    }

    /** @test */
    public function klien_index_view_uses_blue_color_scheme()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        // Check for blue color classes (marketing theme)
        $response->assertSee('bg-blue-800', false);
        $response->assertSee('bg-blue-600', false);
        $response->assertSee('hover:bg-blue-700', false);
    }

    /** @test */
    public function klien_index_view_displays_users_icon()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('fa-users', false);
    }

    /** @test */
    public function klien_index_view_maintains_search_parameter_in_form()
    {
        $response = $this->get(route('klien.index', ['search' => 'Test Search']));

        $response->assertStatus(200);
        $response->assertSee('value="Test Search"', false);
    }

    /** @test */
    public function klien_index_view_maintains_status_parameter_in_form()
    {
        $response = $this->get(route('klien.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertSee('selected', false);
    }

    /** @test */
    public function klien_index_view_shows_empty_state_when_no_data()
    {
        $response = $this->get(route('klien.index'));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada data klien');
        $response->assertSee('Belum ada klien yang terdaftar di sistem');
        $response->assertSee('fa-inbox', false);
    }

    /** @test */
    public function klien_index_view_shows_search_no_results_message()
    {
        $response = $this->get(route('klien.index', ['search' => 'NonExistentKlien']));

        $response->assertStatus(200);
        $response->assertSee('Tidak ditemukan klien dengan kata kunci', false);
        $response->assertSee('NonExistentKlien', false);
    }
}
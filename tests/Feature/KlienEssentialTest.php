<?php

namespace Tests\Feature;

use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienEssentialTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_page_loads_successfully()
    {
        $response = $this->get(route('klien.index'));
        $response->assertStatus(200);
        $response->assertViewIs('pages.marketing.daftar-klien-livewire');
    }

    /** @test */
    public function can_create_new_branch()
    {
        $data = [
            'nama' => 'PT Test Company',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ];

        $response = $this->post(route('klien.store'), $data);
        $response->assertRedirect(route('klien.index'));
        
        $this->assertDatabaseHas('kliens', $data);
    }

    /** @test */
    public function can_update_existing_branch()
    {
        $klien = Klien::create([
            'nama' => 'PT Original',
            'cabang' => 'Original Branch',
            'no_hp' => '081111111111'
        ]);

        $updateData = [
            'nama' => 'PT Updated',
            'cabang' => 'Updated Branch', 
            'no_hp' => '082222222222'
        ];

        $response = $this->put(route('klien.update', $klien), $updateData);
        $response->assertRedirect(route('klien.index'));
        
        $klien->refresh();
        $this->assertEquals('PT Updated', $klien->nama);
        $this->assertEquals('Updated Branch', $klien->cabang);
        $this->assertEquals('082222222222', $klien->no_hp);
    }

    /** @test */
    public function can_soft_delete_branch()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Delete',
            'cabang' => 'Test Branch',
            'no_hp' => '081111111111'
        ]);

        $response = $this->delete(route('klien.destroy', $klien));
        $response->assertRedirect(route('klien.index'));
        
        $this->assertSoftDeleted('kliens', ['id' => $klien->id]);
    }

    /** @test */
    public function can_create_company_via_ajax()
    {
        // Test creating a new company by creating its first branch
        $data = [
            'nama' => 'PT AJAX Test',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ];
        
        $response = $this->postJson(route('klien.store'), $data);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message']);
        
        // Should create both the placeholder and the actual branch
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT AJAX Test',
            'cabang' => 'Kantor Pusat',
            'no_hp' => null
        ]);
        
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT AJAX Test',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);
    }

    /** @test */
    public function ajax_requests_return_json()
    {
        $data = [
            'nama' => 'PT AJAX Branch',
            'cabang' => 'Jakarta', 
            'no_hp' => '081234567890'
        ];
        
        $response = $this->postJson(route('klien.store'), $data);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function regular_requests_redirect()
    {
        $data = [
            'nama' => 'PT Regular Branch',
            'cabang' => 'Surabaya',
            'no_hp' => '081234567891' 
        ];
        
        $response = $this->post(route('klien.store'), $data);
        $response->assertRedirect();
    }

    /** @test */
    public function search_functionality_works()
    {
        Klien::create(['nama' => 'PT Searchable', 'cabang' => 'Jakarta', 'no_hp' => '081111111111']);
        Klien::create(['nama' => 'PT Different', 'cabang' => 'Surabaya', 'no_hp' => '082222222222']);
        
        $response = $this->get(route('klien.index', ['search' => 'Searchable']));
        $response->assertStatus(200);
        $response->assertSee('PT Searchable');
        $response->assertDontSee('PT Different');
    }

    /** @test */
    public function show_page_displays_klien_details()
    {
        $klien = Klien::create([
            'nama' => 'PT Show Test',
            'cabang' => 'Test Branch',
            'no_hp' => '081234567890'
        ]);
        
        $response = $this->get(route('klien.show', $klien));
        $response->assertStatus(200);
        $response->assertViewIs('pages.marketing.klien.show');
        $response->assertSee('PT Show Test');
        $response->assertSee('Test Branch');
        $response->assertSee('081234567890');
    }

    /** @test */
    public function edit_page_displays_form()
    {
        $klien = Klien::create([
            'nama' => 'PT Edit Test', 
            'cabang' => 'Edit Branch',
            'no_hp' => '081234567890'
        ]);
        
        $response = $this->get(route('klien.edit', $klien));
        $response->assertStatus(200);
        $response->assertViewIs('pages.marketing.klien.edit-livewire');
    }
}
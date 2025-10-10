<?php

namespace Tests\Feature;

use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienSmartBranchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_branch_for_existing_company()
    {
        // Create existing company data
        Klien::create(['nama' => 'PT Existing Company', 'cabang' => 'Kantor Pusat', 'no_hp' => null]);

        $data = [
            'nama' => 'PT Existing Company', // existing company
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ];

        $response = $this->post(route('klien.store'), $data);
        $response->assertRedirect(route('klien.index'));
        
        // Should create new branch for existing company
        $this->assertDatabaseHas('kliens', $data);
        
        // Should NOT create duplicate company placeholder
        $this->assertEquals(1, Klien::where('nama', 'PT Existing Company')->where('cabang', 'Kantor Pusat')->count());
    }

    /** @test */
    public function can_create_branch_for_new_company()
    {
        $data = [
            'nama' => 'PT Brand New Company', // new company
            'cabang' => 'Surabaya',
            'no_hp' => '081234567890'
        ];

        $response = $this->post(route('klien.store'), $data);
        $response->assertRedirect(route('klien.index'));
        
        // Should create the branch
        $this->assertDatabaseHas('kliens', $data);
        
        // Should also create company placeholder
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Brand New Company',
            'cabang' => 'Kantor Pusat',
            'no_hp' => null
        ]);
    }

    /** @test */
    public function smart_logic_works_correctly_via_ui_flow()
    {
        // Setup: existing companies
        Klien::create(['nama' => 'PT Alpha', 'cabang' => 'Kantor Pusat', 'no_hp' => null]);
        Klien::create(['nama' => 'PT Alpha', 'cabang' => 'Bandung', 'no_hp' => '081111111111']);

        // Test 1: Add branch to existing company
        $response = $this->post(route('klien.store'), [
            'nama' => 'PT Alpha', // existing
            'cabang' => 'Medan',
            'no_hp' => '082222222222'
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Alpha',
            'cabang' => 'Medan',
            'no_hp' => '082222222222'
        ]);
        
        // Should still have only 1 placeholder
        $this->assertEquals(1, Klien::where('nama', 'PT Alpha')->where('cabang', 'Kantor Pusat')->count());

        // Test 2: Add branch for completely new company
        $response = $this->post(route('klien.store'), [
            'nama' => 'PT Gamma', // new
            'cabang' => 'Yogyakarta',
            'no_hp' => '083333333333'
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Gamma',
            'cabang' => 'Yogyakarta',
            'no_hp' => '083333333333'
        ]);
        
        // Should create new placeholder
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Gamma',
            'cabang' => 'Kantor Pusat',
            'no_hp' => null
        ]);
    }

    /** @test */
    public function form_validation_works_for_both_scenarios()
    {
        // Test missing company name
        $response = $this->post(route('klien.store'), [
            'nama' => '',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);
        
        $response->assertSessionHasErrors(['nama']);

        // Test missing branch location
        $response = $this->post(route('klien.store'), [
            'nama' => 'PT Test',
            'cabang' => '',
            'no_hp' => '081234567890'
        ]);
        
        $response->assertSessionHasErrors(['cabang']);
    }
}
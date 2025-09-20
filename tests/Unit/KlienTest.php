<?php

namespace Tests\Unit;

use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class KlienTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['nama', 'cabang', 'no_hp'];
        
        $klien = new Klien();
        
        $this->assertEquals($fillable, $klien->getFillable());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $klien = new Klien();
        
        $this->assertContains(SoftDeletes::class, class_uses($klien));
    }

    /** @test */
    public function it_can_create_a_klien()
    {
        $klienData = [
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ];

        $klien = Klien::create($klienData);

        $this->assertInstanceOf(Klien::class, $klien);
        $this->assertEquals($klienData['nama'], $klien->nama);
        $this->assertEquals($klienData['cabang'], $klien->cabang);
        $this->assertEquals($klienData['no_hp'], $klien->no_hp);
        $this->assertDatabaseHas('kliens', $klienData);
    }

    /** @test */
    public function it_can_search_by_nama()
    {
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);
        Klien::create(['nama' => 'PT Central Proteina', 'cabang' => 'Balaraja', 'no_hp' => '081234567891']);
        Klien::create(['nama' => 'CJ Feed', 'cabang' => 'Jombang', 'no_hp' => '081234567892']);

        $results = Klien::search('Sreya')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('PT Sreya Sewu', $results->first()->nama);
    }

    /** @test */
    public function it_can_search_by_cabang()
    {
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);
        Klien::create(['nama' => 'PT Central Proteina', 'cabang' => 'Balaraja', 'no_hp' => '081234567891']);
        Klien::create(['nama' => 'PT Charoen Pokpahand', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567892']);

        $results = Klien::search('Sidoarjo')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('cabang', 'Sidoarjo'));
    }

    /** @test */
    public function it_can_search_by_no_hp()
    {
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);
        Klien::create(['nama' => 'PT Central Proteina', 'cabang' => 'Balaraja', 'no_hp' => '081234567891']);

        $results = Klien::search('567890')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('081234567890', $results->first()->no_hp);
    }

    /** @test */
    public function it_can_soft_delete_a_klien()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $klien->delete();

        $this->assertSoftDeleted('kliens', ['id' => $klien->id]);
        $this->assertCount(0, Klien::all());
        $this->assertCount(1, Klien::withTrashed()->get());
    }

    /** @test */
    public function it_can_restore_soft_deleted_klien()
    {
        $klien = Klien::create([
            'nama' => 'PT Test Klien',
            'cabang' => 'Jakarta',
            'no_hp' => '081234567890'
        ]);

        $klien->delete();
        $klien->restore();

        $this->assertCount(1, Klien::all());
        $this->assertNotNull(Klien::find($klien->id));
    }

    /** @test */
    public function search_scope_returns_empty_collection_when_no_matches()
    {
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);

        $results = Klien::search('NonExistentKeyword')->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function search_scope_is_case_insensitive()
    {
        Klien::create(['nama' => 'PT Sreya Sewu', 'cabang' => 'Sidoarjo', 'no_hp' => '081234567890']);

        $results = Klien::search('sreya')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('PT Sreya Sewu', $results->first()->nama);
    }
}
<?php

namespace Tests\Feature;

use App\Models\Klien;
use Database\Seeders\KlienSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function klien_seeder_creates_all_expected_records()
    {
        $this->seed(KlienSeeder::class);

        // Should create 28 klien records as defined in the seeder
        $this->assertDatabaseCount('kliens', 28);
    }

    /** @test */
    public function klien_seeder_creates_records_with_correct_structure()
    {
        $this->seed(KlienSeeder::class);

        $kliens = Klien::all();

        foreach ($kliens as $klien) {
            $this->assertNotNull($klien->nama);
            $this->assertNotNull($klien->cabang);
            $this->assertNotNull($klien->no_hp);
            $this->assertNotNull($klien->created_at);
            $this->assertNotNull($klien->updated_at);
        }
    }

    /** @test */
    public function klien_seeder_creates_specific_companies()
    {
        $this->seed(KlienSeeder::class);

        // Test some specific companies from the CSV data
        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Sreya Sewu',
            'cabang' => 'Sidoarjo'
        ]);

        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Central Proteina',
            'cabang' => 'Balaraja'
        ]);

        $this->assertDatabaseHas('kliens', [
            'nama' => 'CJ Feed',
            'cabang' => 'Jombang'
        ]);

        $this->assertDatabaseHas('kliens', [
            'nama' => 'PT Charoen Pokpahand Indonesia',
            'cabang' => 'Sidoarjo'
        ]);
    }

    /** @test */
    public function klien_seeder_creates_multiple_branches_for_same_company()
    {
        $this->seed(KlienSeeder::class);

        // PT Central Proteina should have multiple branches
        $centralProteinaKliens = Klien::where('nama', 'PT Central Proteina')->get();
        $this->assertGreaterThan(1, $centralProteinaKliens->count());

        $branches = $centralProteinaKliens->pluck('cabang')->toArray();
        $this->assertContains('Balaraja', $branches);
        $this->assertContains('Dupak', $branches);
        $this->assertContains('Sepanjang', $branches);

        // CJ Feed should also have multiple branches
        $cjFeedKliens = Klien::where('nama', 'CJ Feed')->get();
        $this->assertGreaterThan(1, $cjFeedKliens->count());

        $cjBranches = $cjFeedKliens->pluck('cabang')->toArray();
        $this->assertContains('Jombang', $cjBranches);
        $this->assertContains('Semarang', $cjBranches);
        $this->assertContains('Serang', $cjBranches);
    }

    /** @test */
    public function klien_seeder_creates_unique_phone_numbers()
    {
        $this->seed(KlienSeeder::class);

        $phoneNumbers = Klien::pluck('no_hp')->toArray();
        $uniquePhoneNumbers = array_unique($phoneNumbers);

        // All phone numbers should be unique
        $this->assertEquals(count($phoneNumbers), count($uniquePhoneNumbers));
    }

    /** @test */
    public function klien_seeder_phone_numbers_follow_pattern()
    {
        $this->seed(KlienSeeder::class);

        $kliens = Klien::all();

        foreach ($kliens as $klien) {
            // Phone numbers should start with 08123456780 and end with sequential numbers
            $this->assertStringStartsWith('0812345678', $klien->no_hp);
            $this->assertEquals(12, strlen($klien->no_hp)); // Should be 12 digits
        }
    }

    /** @test */
    public function klien_seeder_can_be_run_multiple_times_safely()
    {
        // Run seeder first time
        $this->seed(KlienSeeder::class);
        $firstCount = Klien::count();

        // Run seeder second time
        $this->seed(KlienSeeder::class);
        $secondCount = Klien::count();

        // Should have double the records (since we're creating, not upserting)
        $this->assertEquals($firstCount * 2, $secondCount);
    }

    /** @test */
    public function klien_seeder_creates_records_in_correct_geographical_distribution()
    {
        $this->seed(KlienSeeder::class);

        // Check that we have good geographical distribution
        $uniqueLocations = Klien::distinct('cabang')->pluck('cabang')->toArray();
        
        // Should have multiple different locations
        $this->assertGreaterThan(10, count($uniqueLocations));
        
        // Check some specific key locations from Java
        $this->assertContains('Sidoarjo', $uniqueLocations);
        $this->assertContains('Semarang', $uniqueLocations);
        $this->assertContains('Jakarta', $uniqueLocations);
        $this->assertContains('Pasuruan', $uniqueLocations);
        $this->assertContains('Grobogan', $uniqueLocations);
    }

    /** @test */
    public function klien_seeder_creates_major_feed_companies()
    {
        $this->seed(KlienSeeder::class);

        // These are major feed companies that should be in our seeder
        $majorCompanies = [
            'PT Charoen Pokpahand Indonesia',
            'CJ Feed',
            'PT Central Proteina',
            'PT Cargill',
            'PT Japfa',
            'PT New Hope'
        ];

        foreach ($majorCompanies as $company) {
            $this->assertDatabaseHas('kliens', ['nama' => $company]);
        }
    }
}
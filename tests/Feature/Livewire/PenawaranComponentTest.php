<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Marketing\Penawaran as PenawaranComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenawaranComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_component_loads()
    {
        $response = $this->get('/penawaran');
        $response->assertStatus(200);
    }
}

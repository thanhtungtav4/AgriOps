<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Farm;

class SimpleFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_factory_creates_a_persisted_farm(): void
    {
        $farm = Farm::factory()->create();

        $this->assertTrue($farm->exists);
        $this->assertNotNull($farm->id);
    }
}

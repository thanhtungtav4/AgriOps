<?php

namespace Tests\Feature;

use App\Models\Farm;
use Database\Seeders\AgriFarmRegionalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriFarmRegionalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_three_regional_farms(): void
    {
        $this->seed(AgriFarmRegionalSeeder::class);

        $this->assertSame(3, Farm::query()->count());

        $this->assertDatabaseHas('farms', [
            'code' => 'FARM-CC',
            'name' => 'Nông Trường Củ Chi',
            'climate_zone' => 'tropical',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('farms', [
            'code' => 'FARM-LD',
            'name' => 'Trang Trại Lâm Đồng',
            'climate_zone' => 'highland',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('farms', [
            'code' => 'FARM-MD',
            'name' => 'Nông Trại Măng Đen',
            'climate_zone' => 'highland',
            'status' => 'active',
        ]);
    }

    public function test_it_is_idempotent_and_keeps_regional_profile_metadata(): void
    {
        $this->seed(AgriFarmRegionalSeeder::class);
        $this->seed(AgriFarmRegionalSeeder::class);

        $this->assertSame(3, Farm::query()->count());

        $cuChi = Farm::query()->where('code', 'FARM-CC')->firstOrFail();
        $lamDong = Farm::query()->where('code', 'FARM-LD')->firstOrFail();
        $mangDen = Farm::query()->where('code', 'FARM-MD')->firstOrFail();

        $this->assertSame('Đông Nam Bộ', $cuChi->certification['region']);
        $this->assertSame('Tây Nguyên highland', $lamDong->certification['region']);
        $this->assertSame('Tây Nguyên highland', $mangDen->certification['region']);
        $this->assertStringContainsString('20-30%', $cuChi->certification['profile']);
    }
}

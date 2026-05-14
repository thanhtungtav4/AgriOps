<?php

namespace Tests\Feature;

use App\Models\Crop;
use Database\Seeders\AgriCropCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgriCropCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_every_requested_crop_with_catalog_relations(): void
    {
        $this->seed(AgriCropCatalogSeeder::class);

        $this->assertCount(81, AgriCropCatalogSeeder::CROP_NAMES);

        foreach (AgriCropCatalogSeeder::CROP_NAMES as $name) {
            $crop = Crop::query()
                ->where('name', $name)
                ->with([
                    'varieties',
                    'growthStages',
                    'harvestModels',
                    'irrigationNorms',
                    'laborNorms',
                    'lossProfiles',
                    'standards',
                ])
                ->first();

            $this->assertNotNull($crop, "Missing crop {$name}");
            $this->assertTrue($crop->varieties->where('status', 'active')->isNotEmpty(), "Missing active variety for {$name}");
            $this->assertGreaterThanOrEqual(4, $crop->growthStages->count(), "Missing growth stages for {$name}");
            $this->assertTrue($crop->harvestModels->isNotEmpty(), "Missing harvest model for {$name}");
            $this->assertTrue($crop->irrigationNorms->isNotEmpty(), "Missing irrigation norm for {$name}");
            $this->assertTrue($crop->laborNorms->isNotEmpty(), "Missing labor norm for {$name}");
            $this->assertTrue($crop->lossProfiles->isNotEmpty(), "Missing loss profile for {$name}");
            $this->assertTrue($crop->standards->where('grade', 'A')->isNotEmpty(), "Missing grade A standard for {$name}");
        }
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(AgriCropCatalogSeeder::class);

        $cropCount = Crop::query()->count();
        $varietyCount = \App\Models\CropVariety::query()->count();
        $stageCount = \App\Models\GrowthStage::query()->count();

        $this->seed(AgriCropCatalogSeeder::class);

        $this->assertSame($cropCount, Crop::query()->count());
        $this->assertSame($varietyCount, \App\Models\CropVariety::query()->count());
        $this->assertSame($stageCount, \App\Models\GrowthStage::query()->count());
    }

    public function test_key_crops_have_plausible_agronomic_profiles(): void
    {
        $this->seed(AgriCropCatalogSeeder::class);

        $romain = Crop::query()->where('name', 'Xà lách romain')->firstOrFail();
        $this->assertGreaterThanOrEqual(30, $romain->avg_growth_days);
        $this->assertLessThanOrEqual(60, $romain->avg_growth_days);
        $this->assertSame('highland', $romain->varieties()->firstOrFail()->suitable_climate_zone);

        $potato = Crop::query()->where('name', 'Khoai tây vàng')->firstOrFail();
        $this->assertSame('root', $potato->group);
        $this->assertGreaterThanOrEqual(80, $potato->avg_growth_days);
        $this->assertLessThanOrEqual(130, $potato->avg_growth_days);

        $asparagus = Crop::query()->where('name', 'Măng tây')->firstOrFail();
        $this->assertTrue($asparagus->can_harvest_multiple);
        $this->assertGreaterThanOrEqual(90, $asparagus->harvest_exploitation_days);

        $babyCucumber = Crop::query()->where('name', 'Dưa leo baby')->firstOrFail();
        $this->assertGreaterThanOrEqual(4, (float) $babyCucumber->irrigationNorms()->firstOrFail()->water_amount);

        $bitterMelon = Crop::query()->where('name', 'Khổ qua')->firstOrFail();
        $this->assertSame('tropical', $bitterMelon->varieties()->firstOrFail()->suitable_climate_zone);

        foreach (['Bắp sú tim', 'Cải thảo'] as $name) {
            $crop = Crop::query()->where('name', $name)->firstOrFail();
            $this->assertSame('leafy', $crop->group);
            $this->assertSame('temperate', $crop->varieties()->firstOrFail()->suitable_climate_zone);
        }
    }
}

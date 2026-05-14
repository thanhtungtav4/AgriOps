<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlertsCommand extends Command
{
    protected $signature = 'alerts:check {--farm= : Filter by farm ID}';

    protected $description = 'Check and generate system alerts based on current data';

    public function handle(AlertService $alertService): int
    {
        $farmId = $this->option('farm') ? (int) $this->option('farm') : null;

        $this->info('Checking for alerts...');

        $alerts = $alertService->trigger($farmId);

        if ($alerts->isEmpty()) {
            $this->info('No alerts generated.');
            return Command::SUCCESS;
        }

        $this->info("Generated {$alerts->count()} alerts:");

        $alerts->groupBy('alert_type')->each(function ($alertGroup, $type) {
            $this->line("  - {$type}: {$alertGroup->count()}");
        });

        return Command::SUCCESS;
    }
}
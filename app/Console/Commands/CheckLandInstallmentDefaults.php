<?php

namespace App\Console\Commands;

use App\Services\InstallmentService;
use Illuminate\Console\Command;

class CheckLandInstallmentDefaults extends Command
{
    protected $signature = 'installments:check-land-defaults';

    protected $description = 'For defaulted land plans past the resale wait period, trigger refunds or notify admin.';

    public function handle(InstallmentService $service): int
    {
        $count = $service->checkLandDefaults();
        $this->info("Reviewed {$count} defaulted land plan(s).");

        return self::SUCCESS;
    }
}

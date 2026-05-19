<?php

namespace App\Console\Commands;

use App\Services\InstallmentService;
use Illuminate\Console\Command;

class DetectInstallmentDefaults extends Command
{
    protected $signature = 'installments:detect-defaults';

    protected $description = 'Mark active installment plans whose deadline has passed as defaulted.';

    public function handle(InstallmentService $service): int
    {
        $count = $service->detectDefaults();
        $this->info("Defaulted {$count} plan(s).");

        return self::SUCCESS;
    }
}

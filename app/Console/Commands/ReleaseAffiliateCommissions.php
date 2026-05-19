<?php

namespace App\Console\Commands;

use App\Services\AffiliateService;
use Illuminate\Console\Command;

class ReleaseAffiliateCommissions extends Command
{
    protected $signature = 'affiliate:release-pending-commissions';

    protected $description = 'Promote pending affiliate commissions to available once their hold period has elapsed.';

    public function handle(AffiliateService $service): int
    {
        $count = $service->releaseDueCommissions();

        $this->info("Released {$count} pending commission(s).");

        return self::SUCCESS;
    }
}

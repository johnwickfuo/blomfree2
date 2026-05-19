<?php

namespace App\Console\Commands;

use App\Mail\LowStockDigestAdmin;
use App\Models\Animal;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockDigest extends Command
{
    protected $signature = 'blomfree:low-stock-digest {--threshold=5 : Stock value below which an item is flagged}';

    protected $description = 'Email the admin a digest of variants and pool animals with stock below the threshold';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');

        $variants = ProductVariant::query()
            ->with('product')
            ->where('stock', '<', $threshold)
            ->orderBy('stock')
            ->get();

        $animals = Animal::query()
            ->where('listing_type', 'pool')
            ->where('stock', '<', $threshold)
            ->orderBy('stock')
            ->get();

        if ($variants->isEmpty() && $animals->isEmpty()) {
            $this->info('No low-stock items — nothing to send.');

            return self::SUCCESS;
        }

        $adminEmail = Setting::get('admin_notification_email', 'admin@blomfree.com');
        Mail::to($adminEmail)->send(new LowStockDigestAdmin($variants, $animals));

        $this->info(sprintf(
            'Low-stock digest sent to %s — %d variant(s), %d pool animal(s) under %d',
            $adminEmail,
            $variants->count(),
            $animals->count(),
            $threshold,
        ));

        return self::SUCCESS;
    }
}

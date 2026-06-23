<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Loads admin-managed payment gateway credentials out of the `settings`
 * table and merges them into runtime config. The existing gateway code
 * keeps reading from `config('services.paystack.*')` etc; values stored
 * in settings win over .env values, and missing settings fall back to
 * whatever was already loaded from .env. Secrets are stored encrypted.
 */
class PaymentGatewaySettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The boot() callback runs on every request, including the very
        // first one before migrations have run. Guard against the
        // settings table not existing yet so artisan migrate / fresh
        // installs don't blow up.
        if (! $this->settingsTableExists()) {
            return;
        }

        $this->applyPaystack();
        $this->applyFlutterwave();
    }

    private function applyPaystack(): void
    {
        $public = Setting::get('paystack_public_key');
        $secret = Setting::getSecret('paystack_secret_key');

        if (filled($public)) {
            config(['services.paystack.public_key' => $public]);
        }
        if (filled($secret)) {
            config(['services.paystack.secret_key' => $secret]);
        }
    }

    private function applyFlutterwave(): void
    {
        $public = Setting::get('flutterwave_public_key');
        $secret = Setting::getSecret('flutterwave_secret_key');
        $encryption = Setting::getSecret('flutterwave_encryption_key');
        $hash = Setting::getSecret('flutterwave_secret_hash');

        if (filled($public)) {
            config(['services.flutterwave.public_key' => $public]);
        }
        if (filled($secret)) {
            config(['services.flutterwave.secret_key' => $secret]);
        }
        if (filled($encryption)) {
            config(['services.flutterwave.encryption_key' => $encryption]);
        }
        if (filled($hash)) {
            config(['services.flutterwave.secret_hash' => $hash]);
        }
    }

    private function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}

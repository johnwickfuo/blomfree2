<?php

use App\Models\Cart;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune session carts (and their items, via cascade) that have not been
// touched in over 30 days. Carts are session-bound and orphan once the
// session expires.
Schedule::call(function () {
    Cart::query()->where('updated_at', '<', now()->subDays(30))->delete();
})
    ->daily()
    ->name('prune-stale-carts')
    ->onOneServer();

// Nightly digest of low-stock variants and pool animals.
Schedule::command('blomfree:low-stock-digest')
    ->dailyAt('07:00')
    ->name('low-stock-digest')
    ->onOneServer();

// Daily release of any affiliate commissions whose hold period has elapsed.
Schedule::command('affiliate:release-pending-commissions')
    ->dailyAt('06:00')
    ->name('affiliate-release-commissions')
    ->onOneServer();

// Installment program — daily housekeeping.
Schedule::command('installments:detect-defaults')
    ->dailyAt('00:30')
    ->name('installments-detect-defaults')
    ->onOneServer();

Schedule::command('installments:check-land-defaults')
    ->dailyAt('00:45')
    ->name('installments-check-land-defaults')
    ->onOneServer();

Schedule::command('installments:send-payment-reminders')
    ->dailyAt('08:00')
    ->name('installments-send-payment-reminders')
    ->onOneServer();

Schedule::command('installments:send-deadline-warnings')
    ->dailyAt('08:15')
    ->name('installments-send-deadline-warnings')
    ->onOneServer();

<?php

namespace App\Console\Commands;

use App\Mail\InstallmentPaymentReminder;
use App\Models\InstallmentPlan;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendInstallmentPaymentReminders extends Command
{
    protected $signature = 'installments:send-payment-reminders';

    protected $description = 'Email customers a reminder before each suggested due date.';

    public function handle(): int
    {
        $daysBefore = (int) Setting::get('installment_due_reminder_days_before', '3');
        $target = now()->addDays($daysBefore)->toDateString();
        $count = 0;

        InstallmentPlan::query()
            ->where('status', 'active')
            ->whereNotNull('suggested_schedule')
            ->chunkById(50, function ($plans) use ($target, &$count): void {
                foreach ($plans as $plan) {
                    $schedule = $plan->suggested_schedule ?? [];
                    $changed = false;
                    foreach ($schedule as $i => $entry) {
                        $due = $entry['due_date'] ?? null;
                        $sent = $entry['reminder_sent_at'] ?? null;
                        if ($due === $target && ! $sent) {
                            try {
                                Mail::to($plan->user->email)->send(new InstallmentPaymentReminder(
                                    $plan,
                                    Carbon::parse($due)->format('d M Y'),
                                    (float) ($entry['suggested_amount'] ?? 0),
                                ));
                                $schedule[$i]['reminder_sent_at'] = now()->toIso8601String();
                                $changed = true;
                                $count++;
                            } catch (Throwable $e) {
                                report($e);
                            }
                        }
                    }
                    if ($changed) {
                        $plan->update(['suggested_schedule' => $schedule]);
                    }
                }
            });

        $this->info("Sent {$count} reminder(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Mail\AdminInstallmentDeadlineWarning;
use App\Mail\InstallmentDeadlineWarning;
use App\Models\InstallmentPlan;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendInstallmentDeadlineWarnings extends Command
{
    protected $signature = 'installments:send-deadline-warnings';

    protected $description = 'Email customers (and admin at 7d) when plans approach deadline.';

    public function handle(): int
    {
        $intervals = array_map(
            'intval',
            array_filter(explode(',', (string) Setting::get('installment_overdue_warning_days', '30,14,7,3,1'))),
        );
        $sent = 0;

        foreach ($intervals as $days) {
            $target = now()->addDays($days)->toDateString();

            $plans = InstallmentPlan::query()
                ->where('status', 'active')
                ->whereDate('deadline', $target)
                ->get();

            foreach ($plans as $plan) {
                try {
                    Mail::to($plan->user->email)->send(new InstallmentDeadlineWarning($plan, $days));
                    if ($days === 7) {
                        $adminEmail = Setting::get('installment_admin_notification_email')
                            ?: Setting::get('admin_notification_email', 'admin@blomfree.com');
                        Mail::to($adminEmail)->send(new AdminInstallmentDeadlineWarning($plan, $days));
                    }
                    $sent++;
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        $this->info("Sent {$sent} deadline warning(s).");

        return self::SUCCESS;
    }
}

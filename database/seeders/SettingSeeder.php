<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'inspection_days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'inspection_time_slots' => '9am-11am,11am-1pm,1pm-3pm,3pm-5pm',
            'admin_notification_email' => 'admin@blomfree.com',
            'pickup_address' => "BLOMFREE & CO. NIG. LTD\nYenagoa, Bayelsa State\nCall ahead before arriving.",
            // Affiliate program defaults.
            'affiliate_auto_approve_signup' => '1',
            'affiliate_admin_notification_email' => 'admin@blomfree.com',
            'affiliate_commission_hold_days' => '7',
            'affiliate_commission_hold_fallback_days' => '30',
            'affiliate_minimum_withdrawal' => '5000',
            'affiliate_withdrawal_fee' => '100',
            // Installment program defaults.
            'installment_forfeiture_percentage' => '10',
            'installment_land_resale_wait_days' => '90',
            'installment_signup_enabled' => '1',
            'installment_terms_version' => 'v1.0',
            'installment_admin_notification_email' => 'admin@blomfree.com',
            'installment_due_reminder_days_before' => '3',
            'installment_overdue_warning_days' => '30,14,7,3,1',
        ];

        foreach ($defaults as $key => $value) {
            // Idempotent: only set if missing so admin-customized values
            // survive re-seeds.
            if (Setting::get($key) === null) {
                Setting::set($key, $value);
            }
        }
    }
}

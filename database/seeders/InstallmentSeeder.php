<?php

namespace Database\Seeders;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentTermsAgreement;
use App\Models\Land;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstallmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Enable installment on a couple of lands + a handful of gadgets.
        Land::query()
            ->where('status', 'available')
            ->limit(2)
            ->get()
            ->each(function (Land $l): void {
                $l->update([
                    'installment_enabled' => true,
                    'installment_minimum_down_payment_percentage' => 30,
                    'installment_maximum_length_months' => 12,
                ]);
            });

        Product::query()
            ->where('subsidiary', 'gadgets')
            ->limit(4)
            ->get()
            ->each(function (Product $p, int $i): void {
                $down = [25, 30, 35, 40][$i] ?? 30;
                $months = [6, 4, 3, 5][$i] ?? 4;
                $p->update([
                    'installment_enabled' => true,
                    'installment_minimum_down_payment_percentage' => $down,
                    'installment_maximum_length_months' => $months,
                ]);
            });

        // 2) Create 2 sample customers.
        $alice = User::query()->updateOrCreate(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Customer',
                'phone' => '08020000001',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'bank_name' => 'Access Bank',
                'bank_account_number' => '0011223344',
                'bank_account_name' => 'ALICE CUSTOMER',
            ],
        );
        User::query()->updateOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Customer',
                'phone' => '08020000002',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        );

        // 3) Create one active plan for Alice on a gadget so the admin
        //    immediately sees a populated dashboard.
        $gadget = Product::query()
            ->where('installment_enabled', true)
            ->where('subsidiary', 'gadgets')
            ->first();

        if ($gadget && ! InstallmentPlan::query()->where('user_id', $alice->id)->exists()) {
            $total = (float) $gadget->base_price;
            $minDown = round($total * 0.3, 2);

            $plan = InstallmentPlan::create([
                'user_id' => $alice->id,
                'installable_type' => Product::class,
                'installable_id' => $gadget->id,
                'installable_label' => $gadget->name,
                'subsidiary' => 'gadgets',
                'status' => 'active',
                'total_amount' => $total,
                'amount_paid' => $minDown,
                'minimum_down_payment_amount' => $minDown,
                'minimum_down_payment_percentage' => 30,
                'down_payment_paid' => true,
                'maximum_length_months' => 6,
                'deadline' => now()->copy()->addMonths(6)->toDateString(),
                'forfeiture_percentage' => 10,
                'terms_accepted_at' => now()->subDays(2),
                'terms_acceptance_ip' => '127.0.0.1',
                'terms_version' => 'v1.0',
                'requested_at' => now()->subDays(2),
                'activated_at' => now()->subDays(2),
            ]);

            InstallmentPayment::create([
                'installment_plan_id' => $plan->id,
                'amount' => $minDown,
                'payment_gateway' => 'paystack',
                'payment_reference' => 'PSK-SEED-1',
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(2),
                'is_down_payment' => true,
            ]);

            InstallmentTermsAgreement::create([
                'user_id' => $alice->id,
                'installment_plan_id' => $plan->id,
                'terms_version' => 'v1.0',
                'terms_text_snapshot' => '(seeded — see live terms)',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'seeder',
                'accepted_at' => now()->subDays(2),
            ]);
        }
    }
}

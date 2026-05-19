<?php

namespace App\Services;

use App\Mail\AdminInstallmentDefaulted;
use App\Mail\AdminInstallmentCompleted;
use App\Mail\AdminInstallmentRefundRequest;
use App\Mail\AdminNewInstallmentRequest;
use App\Mail\InstallmentActivated;
use App\Mail\InstallmentApproved;
use App\Mail\InstallmentCancelled;
use App\Mail\InstallmentCompleted;
use App\Mail\InstallmentDefaulted;
use App\Mail\InstallmentFulfilled;
use App\Mail\InstallmentPaymentReceived;
use App\Mail\InstallmentRefundProcessed;
use App\Mail\InstallmentRejected;
use App\Mail\InstallmentRequestReceived;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentRefund;
use App\Models\InstallmentTermsAgreement;
use App\Models\Land;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InstallmentService
{
    public const TERMS_TEMPLATE = <<<'TERMS'
BLOMFREE INSTALLMENT TERMS — Version %s

You are entering an installment plan with BLOMFREE & CO. NIG. LTD for the
purchase of %s. The total committed amount is NGN %s. You must pay a down
payment of at least NGN %s (%d%% of total) within your dashboard before the
plan activates. Once active, you may pay any amount toward the balance at any
time until the deadline.

DEADLINE: You have %d months from activation to complete the plan. The
specific deadline date is %s. If you do not complete by this date, the plan
defaults. We retain %d%% of every Naira you have paid as a forfeiture fee, and
the remaining %d%% is refunded to your bank account on file. The same %d%%
forfeiture applies if you voluntarily cancel an active plan.

LANDS: Approved land installments reserve the plot for you. If you default,
the land returns to our public listings. Refund of your 90%% share is
processed once the land is resold, or after %d days at admin discretion.

GADGETS: Stock is not reserved. If, upon completion of your plan, the item
is unavailable, you may choose to wait for restock or receive a full refund
(no forfeiture). Otherwise the standard 90/10 split applies on default.

EARLY COMPLETION is always permitted without penalty.

REFUNDS are processed by manual bank transfer and may take 7–14 business days.
Please verify your bank details on file before triggering a refund.

DISPUTES: Contact admin@blomfree.com.
TERMS;

    /**
     * Create an InstallmentPlan from a customer's request.
     *
     * @param  array<string, mixed>  $data  Expected: installable (Model), user, affiliate_code (optional), request_metadata
     */
    public function createPlan(array $data): InstallmentPlan
    {
        /** @var Model $installable */
        $installable = $data['installable'];
        /** @var User $user */
        $user = $data['user'];

        $pricing = $this->computePricing($installable, $data['affiliate_code'] ?? null);

        return DB::transaction(function () use ($data, $installable, $user, $pricing): InstallmentPlan {
            $subsidiary = $installable instanceof Land ? 'lands' : 'gadgets';
            $status = $subsidiary === 'lands' ? 'pending_approval' : 'awaiting_down_payment';

            $downPaymentPct = $this->installmentMinDownPct($installable);
            $maxMonths = $this->installmentMaxMonths($installable);
            $total = $pricing['price'];
            $minDown = round($total * $downPaymentPct / 100, 2);
            $forfeiturePct = (int) Setting::get('installment_forfeiture_percentage', '10');
            $version = (string) Setting::get('installment_terms_version', 'v1.0');

            $plan = InstallmentPlan::create([
                'user_id' => $user->id,
                'installable_type' => $installable->getMorphClass(),
                'installable_id' => $installable->getKey(),
                'installable_label' => $this->labelFor($installable),
                'subsidiary' => $subsidiary,
                'status' => $status,
                'total_amount' => $total,
                'minimum_down_payment_amount' => $minDown,
                'minimum_down_payment_percentage' => $downPaymentPct,
                'maximum_length_months' => $maxMonths,
                'forfeiture_percentage' => $forfeiturePct,
                'affiliate_id' => $pricing['affiliate']?->id,
                'affiliate_code_used' => $pricing['affiliate']?->code,
                'affiliate_commission_locked' => $pricing['commission'],
                'terms_accepted_at' => now(),
                'terms_acceptance_ip' => (string) ($data['request_metadata']['ip'] ?? '0.0.0.0'),
                'terms_version' => $version,
                'requested_at' => now(),
                'suggested_schedule' => $this->buildSuggestedSchedule($total, $minDown, $maxMonths),
                'admin_notes' => $data['notes'] ?? null,
            ]);

            // Capture the legal terms snapshot.
            InstallmentTermsAgreement::create([
                'user_id' => $user->id,
                'installment_plan_id' => $plan->id,
                'terms_version' => $version,
                'terms_text_snapshot' => $this->termsTextFor($plan),
                'ip_address' => (string) ($data['request_metadata']['ip'] ?? '0.0.0.0'),
                'user_agent' => substr((string) ($data['request_metadata']['user_agent'] ?? ''), 0, 500),
                'accepted_at' => now(),
            ]);

            if ($subsidiary === 'lands') {
                $this->safeMail($user->email, new InstallmentRequestReceived($plan));
                $this->safeMail($this->adminEmail(), new AdminNewInstallmentRequest($plan));
            }

            return $plan;
        });
    }

    public function approveLandPlan(InstallmentPlan $plan, ?array $customSchedule = null, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($plan, $customSchedule, $adminNotes): void {
            $plan->update([
                'status' => 'awaiting_down_payment',
                'approved_at' => now(),
                'suggested_schedule' => $customSchedule ?: $plan->suggested_schedule,
                'admin_notes' => trim(($plan->admin_notes ? $plan->admin_notes."\n" : '').($adminNotes ?: '')),
            ]);

            $this->safeMail($plan->user->email, new InstallmentApproved($plan));
        });
    }

    public function rejectLandPlan(InstallmentPlan $plan, string $reason): void
    {
        DB::transaction(function () use ($plan, $reason): void {
            $plan->update([
                'status' => 'cancelled_by_customer',
                'cancelled_at' => now(),
                'admin_notes' => trim(($plan->admin_notes ? $plan->admin_notes."\n" : '').'Rejected: '.$reason),
            ]);
            $this->safeMail($plan->user->email, new InstallmentRejected($plan, $reason));
        });
    }

    /**
     * Record a successful payment against a plan. Idempotent by gateway reference.
     *
     * @param  array<string, mixed>  $data  amount, payment_gateway, payment_reference, gateway_response
     */
    public function recordPayment(InstallmentPlan $plan, array $data): InstallmentPayment
    {
        $existing = InstallmentPayment::query()
            ->where('payment_reference', $data['payment_reference'])
            ->where('payment_gateway', $data['payment_gateway'])
            ->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($plan, $data): InstallmentPayment {
            $fresh = InstallmentPlan::query()->whereKey($plan->id)->lockForUpdate()->first();

            if (! $fresh->canAcceptPayment()) {
                abort(422, 'This plan no longer accepts payments.');
            }

            $remaining = (float) $fresh->total_amount - (float) $fresh->amount_paid;
            $amount = min((float) $data['amount'], $remaining);
            if ($amount <= 0) {
                abort(422, 'Plan already fully paid.');
            }

            $wouldBeDownPayment = ! $fresh->down_payment_paid
                && (float) $fresh->amount_paid + $amount >= (float) $fresh->minimum_down_payment_amount;

            $payment = InstallmentPayment::create([
                'installment_plan_id' => $fresh->id,
                'amount' => $amount,
                'payment_gateway' => $data['payment_gateway'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_status' => 'paid',
                'paid_at' => now(),
                'is_down_payment' => $wouldBeDownPayment,
                'gateway_response' => $data['gateway_response'] ?? null,
            ]);

            $newPaid = (float) $fresh->amount_paid + $amount;
            $update = ['amount_paid' => (string) $newPaid];

            if ($wouldBeDownPayment) {
                $update['down_payment_paid'] = true;
            }

            if ($fresh->status === 'awaiting_down_payment' && $wouldBeDownPayment) {
                $update['status'] = 'active';
                $update['activated_at'] = now();
                $update['deadline'] = now()->copy()->addMonths($fresh->maximum_length_months)->toDateString();
            }

            if ($newPaid >= (float) $fresh->total_amount - 0.01) {
                $update['status'] = 'completed';
                $update['completed_at'] = now();
            }

            $fresh->update($update);
            $fresh->refresh();

            // Land reservation on activation.
            if ($wouldBeDownPayment && $fresh->subsidiary === 'lands' && $fresh->installable instanceof Land) {
                $fresh->installable->update(['status' => 'reserved_installment']);
            }

            $this->safeMail($fresh->user->email, new InstallmentPaymentReceived($fresh->fresh(), $payment));

            if ($update['status'] ?? null === 'active') {
                $this->safeMail($fresh->user->email, new InstallmentActivated($fresh->fresh()));
            }

            if (($update['status'] ?? null) === 'completed') {
                $this->onPlanCompleted($fresh->fresh());
            }

            return $payment;
        });
    }

    /**
     * Plan reached `completed`: handle gadget stock check, fulfillment routing,
     * and affiliate commission creation.
     */
    public function onPlanCompleted(InstallmentPlan $plan): void
    {
        // For gadgets, check if stock is available — if so transition to
        // awaiting_fulfillment immediately; if not, leave at completed so the
        // customer can pick wait-or-refund from their dashboard.
        if ($plan->subsidiary === 'gadgets') {
            $stock = match (true) {
                $plan->installable instanceof ProductVariant => (int) ($plan->installable->stock ?? 0),
                $plan->installable instanceof Product => (int) ($plan->installable->stock ?? 0),
                default => 0,
            };

            if ($stock > 0) {
                $plan->update(['status' => 'awaiting_fulfillment']);
                $this->decrementStock($plan->installable);
            }
        } else {
            // Lands: move straight to awaiting_fulfillment so admin handles
            // document transfer.
            $plan->update(['status' => 'awaiting_fulfillment']);
        }

        // Award affiliate commission if applicable (always pending+held until
        // fulfillment, then released by normal affiliate flow).
        if ($plan->affiliate_id && (float) $plan->affiliate_commission_locked > 0) {
            $this->awardAffiliateCommission($plan);
        }

        $this->safeMail($plan->user->email, new InstallmentCompleted($plan));
        $this->safeMail($this->adminEmail(), new AdminInstallmentCompleted($plan));
    }

    public function markFulfilled(InstallmentPlan $plan, ?string $note = null): void
    {
        DB::transaction(function () use ($plan, $note): void {
            $plan->update([
                'status' => 'fulfilled',
                'fulfilled_at' => now(),
                'admin_notes' => trim(($plan->admin_notes ? $plan->admin_notes."\n" : '').'Fulfilled'.($note ? ': '.$note : '')),
            ]);

            if ($plan->subsidiary === 'lands' && $plan->installable instanceof Land) {
                $plan->installable->update(['status' => 'sold']);
            }

            $this->safeMail($plan->user->email, new InstallmentFulfilled($plan));
        });
    }

    public function customerCancel(InstallmentPlan $plan): InstallmentRefund
    {
        if (! in_array($plan->status, ['awaiting_down_payment', 'active'], true)) {
            abort(422, 'This plan cannot be cancelled.');
        }

        return DB::transaction(function () use ($plan): InstallmentRefund {
            $plan->update([
                'status' => 'cancelled_by_customer',
                'cancelled_at' => now(),
            ]);

            if ($plan->subsidiary === 'lands' && $plan->installable instanceof Land) {
                $plan->installable->update(['status' => 'available']);
            }

            $this->safeMail($plan->user->email, new InstallmentCancelled($plan));

            return $this->createRefund($plan, 'customer_cancelled');
        });
    }

    public function markDefaulted(InstallmentPlan $plan): void
    {
        if ($plan->status !== 'active') {
            return;
        }

        DB::transaction(function () use ($plan): void {
            $plan->update([
                'status' => 'defaulted',
                'defaulted_at' => now(),
            ]);

            $this->safeMail($plan->user->email, new InstallmentDefaulted($plan));
            $this->safeMail($this->adminEmail(), new AdminInstallmentDefaulted($plan));

            if ($plan->subsidiary === 'lands' && $plan->installable instanceof Land) {
                $plan->installable->update(['status' => 'available']);
                // Land refund deferred until resale or admin override after wait days.
            } elseif ($plan->subsidiary === 'gadgets') {
                // Gadgets refund immediately (stock wasn't reserved).
                $this->createRefund($plan, 'gadget_default');
            }
        });
    }

    public function createRefund(InstallmentPlan $plan, string $trigger, ?float $overrideRefundAmount = null): InstallmentRefund
    {
        $paid = (float) $plan->amount_paid;
        $forfeitPct = $trigger === 'item_unavailable_at_completion'
            ? 0
            : (int) $plan->forfeiture_percentage;
        $forfeiture = round($paid * $forfeitPct / 100, 2);
        $refund = $overrideRefundAmount ?? round($paid - $forfeiture, 2);

        $user = $plan->user;
        $refundRecord = InstallmentRefund::create([
            'installment_plan_id' => $plan->id,
            'amount_paid_total' => (string) $paid,
            'forfeiture_amount' => (string) $forfeiture,
            'refund_amount' => (string) max(0, $refund),
            'triggered_by' => $trigger,
            'status' => 'pending',
            'bank_snapshot' => $user?->hasBankDetails() ? [
                'bank_name' => $user->bank_name,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_name' => $user->bank_account_name,
            ] : null,
            'requested_at' => now(),
        ]);

        $plan->update(['status' => 'awaiting_refund']);

        $this->safeMail($this->adminEmail(), new AdminInstallmentRefundRequest($refundRecord));

        return $refundRecord;
    }

    public function markRefundPaid(InstallmentRefund $refund, string $paymentReference, ?string $notes = null): void
    {
        DB::transaction(function () use ($refund, $paymentReference, $notes): void {
            $fresh = InstallmentRefund::query()->whereKey($refund->id)->lockForUpdate()->first();
            if (! $fresh || $fresh->status === 'paid') {
                return;
            }
            $fresh->update([
                'status' => 'paid',
                'payment_reference' => $paymentReference,
                'admin_notes' => $notes,
                'processed_at' => now(),
            ]);
            $fresh->plan->update(['status' => 'refunded']);

            $this->safeMail($fresh->plan->user->email, new InstallmentRefundProcessed($fresh));
        });
    }

    public function rejectRefund(InstallmentRefund $refund, string $reason): void
    {
        $refund->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'processed_at' => now(),
        ]);
    }

    public function awardAffiliateCommission(InstallmentPlan $plan): void
    {
        if (! $plan->affiliate_id || (float) $plan->affiliate_commission_locked <= 0) {
            return;
        }
        if (AffiliateCommission::query()->where('order_id', null)->where('order_item_id', $plan->id)->exists()) {
            // (Defensive; we don't actually use these columns for installments.)
            return;
        }

        DB::transaction(function () use ($plan): void {
            $affiliate = Affiliate::query()->whereKey($plan->affiliate_id)->lockForUpdate()->first();
            if (! $affiliate) {
                return;
            }
            // Per Phase 14 schema commissions require order_id/order_item_id.
            // We can't add a commission without those, so we represent the
            // installment commission by updating the affiliate's balances and
            // emailing them; the audit trail lives on the plan itself.
            $amount = (float) $plan->affiliate_commission_locked;
            $affiliate->update([
                'pending_balance' => (string) ((float) $affiliate->pending_balance + $amount),
                'total_earned' => (string) ((float) $affiliate->total_earned + $amount),
            ]);
        });
    }

    /**
     * Daily: detect plans whose deadline has passed and default them.
     */
    public function detectDefaults(): int
    {
        $today = now()->startOfDay();
        $plans = InstallmentPlan::query()
            ->where('status', 'active')
            ->whereNotNull('deadline')
            ->where('deadline', '<', $today->toDateString())
            ->get();

        foreach ($plans as $plan) {
            try {
                $this->markDefaulted($plan);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $plans->count();
    }

    /**
     * Daily: for defaulted land plans past the wait period, trigger refunds
     * (either via land resale or admin notification).
     */
    public function checkLandDefaults(): int
    {
        $waitDays = (int) Setting::get('installment_land_resale_wait_days', '90');
        $cutoff = now()->subDays($waitDays);

        $plans = InstallmentPlan::query()
            ->where('status', 'defaulted')
            ->where('subsidiary', 'lands')
            ->where('defaulted_at', '<=', $cutoff)
            ->whereDoesntHave('refund')
            ->get();

        foreach ($plans as $plan) {
            try {
                $land = $plan->installable;
                if ($land instanceof Land && $land->status === 'sold') {
                    $this->createRefund($plan, 'land_resold');
                } else {
                    // Admin email; do nothing automatic.
                    Log::info("Land plan {$plan->reference} defaulted >$waitDays days without resale. Admin decision needed.");
                    $this->safeMail($this->adminEmail(), new AdminInstallmentDefaulted($plan->fresh()));
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $plans->count();
    }

    public function buildSuggestedSchedule(float $total, float $downPayment, int $months): array
    {
        $start = now()->startOfDay();
        $remaining = max(0.0, $total - $downPayment);
        $perMonth = $months > 0 ? round($remaining / $months, 2) : 0;

        $schedule = [
            [
                'due_date' => $start->toDateString(),
                'suggested_amount' => $downPayment,
                'label' => 'Down payment',
            ],
        ];
        for ($i = 1; $i <= $months; $i++) {
            $schedule[] = [
                'due_date' => $start->copy()->addMonths($i)->toDateString(),
                'suggested_amount' => $perMonth,
                'label' => "Month {$i}",
            ];
        }
        return $schedule;
    }

    public function termsTextFor(InstallmentPlan $plan): string
    {
        $remaining = max(0.0, (float) $plan->total_amount - (float) $plan->minimum_down_payment_amount);
        return sprintf(
            self::TERMS_TEMPLATE,
            $plan->terms_version,
            $plan->installable_label,
            number_format((float) $plan->total_amount, 2),
            number_format((float) $plan->minimum_down_payment_amount, 2),
            $plan->minimum_down_payment_percentage,
            $plan->maximum_length_months,
            $plan->deadline?->toDateString() ?? '(set on activation)',
            $plan->forfeiture_percentage,
            100 - $plan->forfeiture_percentage,
            $plan->forfeiture_percentage,
            (int) Setting::get('installment_land_resale_wait_days', '90'),
        );
    }

    /**
     * @return array{price: float, affiliate: ?Affiliate, commission: float}
     */
    private function computePricing(Model $installable, ?string $affiliateCode): array
    {
        $normalPrice = $this->normalPriceFor($installable);
        $affiliate = $affiliateCode ? app(AffiliateService::class)->findByCode($affiliateCode) : null;

        if (! $affiliate) {
            return ['price' => $normalPrice, 'affiliate' => null, 'commission' => 0.0];
        }

        $affPrice = $this->affiliatePriceFor($installable);
        $affCommission = $this->affiliateCommissionFor($installable);

        if ($affPrice === null || $affPrice > $normalPrice) {
            return ['price' => $normalPrice, 'affiliate' => null, 'commission' => 0.0];
        }

        return [
            'price' => $affPrice,
            'affiliate' => $affiliate,
            'commission' => (float) ($affCommission ?? 0),
        ];
    }

    private function normalPriceFor(Model $installable): float
    {
        if ($installable instanceof Land) {
            return (float) ($installable->price_per_plot * ($installable->number_of_plots ?: 1));
        }
        if ($installable instanceof ProductVariant) {
            return $installable->effectivePrice();
        }
        if ($installable instanceof Product) {
            return (float) $installable->base_price;
        }
        return 0.0;
    }

    private function affiliatePriceFor(Model $installable): ?float
    {
        if ($installable instanceof ProductVariant) {
            return $installable->effectiveAffiliatePrice();
        }
        if ($installable instanceof Product) {
            return $installable->affiliate_price !== null ? (float) $installable->affiliate_price : null;
        }
        return null;
    }

    private function affiliateCommissionFor(Model $installable): ?float
    {
        if ($installable instanceof ProductVariant) {
            return $installable->effectiveAffiliateCommission();
        }
        if ($installable instanceof Product) {
            return $installable->affiliate_commission !== null ? (float) $installable->affiliate_commission : null;
        }
        return null;
    }

    private function installmentMinDownPct(Model $installable): int
    {
        return (int) ($installable->effectiveMinimumDownPaymentPercentage() ?? 30);
    }

    private function installmentMaxMonths(Model $installable): int
    {
        return (int) ($installable->effectiveMaximumLengthMonths() ?? 6);
    }

    private function labelFor(Model $installable): string
    {
        if ($installable instanceof Land) {
            return $installable->title.' — '.$installable->location_address;
        }
        if ($installable instanceof ProductVariant) {
            return ($installable->product?->name ?? 'Product').' — '.$installable->attributes_label;
        }
        if ($installable instanceof Product) {
            return $installable->name;
        }
        return 'Item';
    }

    private function decrementStock(Model $installable): void
    {
        if ($installable instanceof ProductVariant) {
            $fresh = ProductVariant::query()->whereKey($installable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => max(0, (int) $fresh->stock - 1)]);
            }
        } elseif ($installable instanceof Product) {
            $fresh = Product::query()->whereKey($installable->id)->lockForUpdate()->first();
            if ($fresh) {
                $fresh->update(['stock' => max(0, (int) ($fresh->stock ?? 0) - 1)]);
            }
        }
    }

    private function adminEmail(): string
    {
        return (string) (Setting::get('installment_admin_notification_email')
            ?: Setting::get('admin_notification_email', 'admin@blomfree.com'));
    }

    private function safeMail(?string $address, object $mailable): void
    {
        if (! $address) {
            return;
        }
        try {
            Mail::to($address)->send($mailable);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

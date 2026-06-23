<?php

namespace App\Services;

use App\Mail\AffiliateCommissionEarned;
use App\Mail\AffiliateCommissionReversed;
use App\Mail\AffiliateSuspended;
use App\Mail\AffiliateWelcomeEmail;
use App\Mail\AffiliateWithdrawalPaid;
use App\Mail\AffiliateWithdrawalRejected;
use App\Mail\AffiliateWithdrawalRequested;
use App\Mail\AdminNewAffiliateSignup;
use App\Models\Affiliate;
use App\Models\AffiliateBalanceAdjustment;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawal;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AffiliateService
{
    /**
     * Sign up a brand-new affiliate. Creates the User and Affiliate atomically
     * and returns the Affiliate.
     *
     * @param  array<string, mixed>  $data
     */
    public function signup(array $data): Affiliate
    {
        return DB::transaction(function () use ($data): Affiliate {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'is_admin' => false,
                'is_affiliate' => true,
            ]);

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'whatsapp_number' => $data['whatsapp_number'] ?? $data['phone'],
                'social_handles' => $data['social_handles'] ?? [],
                'status' => Setting::get('affiliate_auto_approve_signup', '1') === '0'
                    ? 'suspended'
                    : 'active',
                'joined_at' => now(),
            ]);

            $this->safeMail($user->email, new AffiliateWelcomeEmail($affiliate));

            $adminEmail = Setting::get('affiliate_admin_notification_email')
                ?: Setting::get('admin_notification_email', 'admin@blomfree.com');
            $this->safeMail($adminEmail, new AdminNewAffiliateSignup($affiliate));

            return $affiliate;
        });
    }

    /**
     * Upgrade an existing logged-in customer into an affiliate. Flips
     * is_affiliate on the user and creates the Affiliate row + welcome
     * emails. Used by the /account/affiliate/become flow.
     *
     * @param  array<string, mixed>  $data
     */
    public function createForExistingUser(User $user, array $data): Affiliate
    {
        return DB::transaction(function () use ($user, $data): Affiliate {
            if ($user->affiliate) {
                return $user->affiliate;
            }

            $user->update(['is_affiliate' => true]);

            $affiliate = Affiliate::create([
                'user_id' => $user->id,
                'whatsapp_number' => $data['whatsapp_number'] ?? $user->phone,
                'social_handles' => $data['social_handles'] ?? [],
                'status' => Setting::get('affiliate_auto_approve_signup', '1') === '0'
                    ? 'suspended'
                    : 'active',
                'joined_at' => now(),
            ]);

            $this->safeMail($user->email, new AffiliateWelcomeEmail($affiliate));

            $adminEmail = Setting::get('affiliate_admin_notification_email')
                ?: Setting::get('admin_notification_email', 'admin@blomfree.com');
            $this->safeMail($adminEmail, new AdminNewAffiliateSignup($affiliate));

            return $affiliate;
        });
    }

    /**
     * Validate an affiliate code submitted at checkout. Returns the active
     * Affiliate or null. Suspended affiliates are treated as not-found so the
     * frontend never reveals their suspension status.
     */
    public function findByCode(?string $code): ?Affiliate
    {
        if (! $code) {
            return null;
        }
        $affiliate = Affiliate::query()->where('code', strtoupper(trim($code)))->first();

        return ($affiliate && $affiliate->isActive()) ? $affiliate : null;
    }

    /**
     * Apply commissions for every eligible OrderItem on a freshly-paid order.
     * Idempotent: skips if commissions already exist for this order.
     */
    public function accrueForPaidOrder(Order $order): void
    {
        if (! $order->affiliate_id) {
            return;
        }
        if (AffiliateCommission::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $holdDays = (int) Setting::get('affiliate_commission_hold_days', '7');
        $fallbackDays = (int) Setting::get('affiliate_commission_hold_fallback_days', '30');

        DB::transaction(function () use ($order, $fallbackDays): void {
            $affiliate = Affiliate::query()->whereKey($order->affiliate_id)->lockForUpdate()->first();
            if (! $affiliate) {
                return;
            }

            $earned = (float) $order->paid_at?->diffInSeconds(now()) >= 0
                ? ($order->paid_at ?? now())
                : now();

            $availableAt = ($order->paid_at ?? now())->copy()->addDays($fallbackDays);

            $totalAccrued = 0.0;
            foreach ($order->items as $item) {
                if (! $item->affiliate_eligible || (float) $item->affiliate_unit_commission <= 0) {
                    continue;
                }

                $amount = (float) $item->affiliate_unit_commission * (int) $item->quantity;
                $totalAccrued += $amount;

                AffiliateCommission::create([
                    'affiliate_id' => $affiliate->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'amount' => (string) $amount,
                    'status' => 'pending',
                    'earned_at' => $order->paid_at ?? now(),
                    'available_at' => $availableAt,
                ]);
            }

            if ($totalAccrued > 0) {
                $affiliate->update([
                    'pending_balance' => (string) ((float) $affiliate->pending_balance + $totalAccrued),
                    'total_earned' => (string) ((float) $affiliate->total_earned + $totalAccrued),
                ]);

                $this->safeMail($affiliate->user?->email, new AffiliateCommissionEarned($affiliate, $order, $totalAccrued));
            }
        });
    }

    /**
     * When an order is delivered, pull `available_at` in to delivered_at + holdDays
     * (whichever is sooner than the fallback that was set on accrual).
     */
    public function onOrderDelivered(Order $order): void
    {
        if (! $order->affiliate_id) {
            return;
        }

        $holdDays = (int) Setting::get('affiliate_commission_hold_days', '7');
        $newAvailable = ($order->delivered_at ?? now())->copy()->addDays($holdDays);

        AffiliateCommission::query()
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->each(function (AffiliateCommission $c) use ($newAvailable): void {
                if ($newAvailable->lt($c->available_at)) {
                    $c->update(['available_at' => $newAvailable]);
                }
            });
    }

    /**
     * Reverse all non-withdrawn commissions for a cancelled/refunded order.
     */
    public function reverseForOrder(Order $order, string $reason): void
    {
        if (! $order->affiliate_id) {
            return;
        }

        DB::transaction(function () use ($order, $reason): void {
            $affiliate = Affiliate::query()->whereKey($order->affiliate_id)->lockForUpdate()->first();
            if (! $affiliate) {
                return;
            }

            $totalReversed = 0.0;
            $commissions = AffiliateCommission::query()
                ->where('order_id', $order->id)
                ->whereIn('status', ['pending', 'available'])
                ->get();

            foreach ($commissions as $c) {
                $amount = (float) $c->amount;
                $wasPending = $c->status === 'pending';

                $c->update([
                    'status' => 'reversed',
                    'reversed_at' => now(),
                    'reversal_reason' => $reason,
                ]);

                if ($wasPending) {
                    $affiliate->update([
                        'pending_balance' => (string) ((float) $affiliate->pending_balance - $amount),
                    ]);
                } else {
                    $affiliate->update([
                        'available_balance' => (string) ((float) $affiliate->available_balance - $amount),
                    ]);
                }

                $affiliate->update([
                    'total_earned' => (string) ((float) $affiliate->total_earned - $amount),
                ]);
                $totalReversed += $amount;
            }

            if ($totalReversed > 0) {
                if ((float) $affiliate->fresh()->available_balance < 0 || (float) $affiliate->fresh()->pending_balance < 0) {
                    Log::warning('Affiliate balance went negative after reversal', [
                        'affiliate' => $affiliate->code,
                        'order' => $order->reference,
                    ]);
                    $adminEmail = Setting::get('affiliate_admin_notification_email')
                        ?: Setting::get('admin_notification_email', 'admin@blomfree.com');
                    $this->safeMail($adminEmail, new AffiliateCommissionReversed($affiliate, $order, $totalReversed, true));
                }
                $this->safeMail($affiliate->user?->email, new AffiliateCommissionReversed($affiliate, $order, $totalReversed, false));
            }
        });
    }

    /**
     * Daily release: promote pending commissions to available when their
     * available_at has passed. Returns the number released.
     */
    public function releaseDueCommissions(?Carbon $asOf = null): int
    {
        $asOf ??= now();

        $due = AffiliateCommission::query()
            ->where('status', 'pending')
            ->where('available_at', '<=', $asOf)
            ->get();

        $count = 0;
        foreach ($due->groupBy('affiliate_id') as $affiliateId => $commissions) {
            DB::transaction(function () use ($affiliateId, $commissions, &$count): void {
                $affiliate = Affiliate::query()->whereKey($affiliateId)->lockForUpdate()->first();
                if (! $affiliate) {
                    return;
                }

                $total = (float) $commissions->sum(fn ($c): float => (float) $c->amount);
                AffiliateCommission::query()
                    ->whereIn('id', $commissions->pluck('id'))
                    ->update(['status' => 'available']);

                $affiliate->update([
                    'pending_balance' => (string) ((float) $affiliate->pending_balance - $total),
                    'available_balance' => (string) ((float) $affiliate->available_balance + $total),
                ]);

                $count += $commissions->count();
            });
        }

        return $count;
    }

    /**
     * Customer-side: request a withdrawal. Deducts from available immediately;
     * refunded on rejection.
     */
    public function requestWithdrawal(Affiliate $affiliate, float $amount): AffiliateWithdrawal
    {
        if (! $affiliate->isActive()) {
            abort(403, 'Your account is suspended.');
        }
        if (blank($affiliate->bank_name) || blank($affiliate->bank_account_number)) {
            abort(422, 'Please set your bank details before requesting a withdrawal.');
        }
        if (AffiliateWithdrawal::query()
            ->where('affiliate_id', $affiliate->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists()
        ) {
            abort(422, 'You already have a withdrawal in progress.');
        }

        $minimum = (float) Setting::get('affiliate_minimum_withdrawal', '5000');
        $fee = (float) Setting::get('affiliate_withdrawal_fee', '100');

        if ($amount < $minimum) {
            abort(422, 'Minimum withdrawal is NGN '.number_format($minimum, 0).'.');
        }

        return DB::transaction(function () use ($affiliate, $amount, $fee): AffiliateWithdrawal {
            $fresh = Affiliate::query()->whereKey($affiliate->id)->lockForUpdate()->first();
            if ((float) $fresh->available_balance < $amount) {
                abort(422, 'Withdrawal exceeds your available balance.');
            }

            $net = max(0, $amount - $fee);

            $withdrawal = AffiliateWithdrawal::create([
                'affiliate_id' => $fresh->id,
                'amount' => (string) $amount,
                'fee' => (string) $fee,
                'net_amount' => (string) $net,
                'bank_snapshot' => [
                    'bank_name' => $fresh->bank_name,
                    'bank_account_number' => $fresh->bank_account_number,
                    'bank_account_name' => $fresh->bank_account_name,
                ],
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            $fresh->update([
                'available_balance' => (string) ((float) $fresh->available_balance - $amount),
                'total_withdrawn' => (string) ((float) $fresh->total_withdrawn + $amount),
            ]);

            $adminEmail = Setting::get('affiliate_admin_notification_email')
                ?: Setting::get('admin_notification_email', 'admin@blomfree.com');
            $this->safeMail($adminEmail, new AffiliateWithdrawalRequested($withdrawal));

            return $withdrawal;
        });
    }

    /**
     * Admin marks a withdrawal as paid (after sending bank transfer).
     */
    public function markWithdrawalPaid(AffiliateWithdrawal $withdrawal, string $paymentReference, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($withdrawal, $paymentReference, $adminNotes): void {
            $fresh = AffiliateWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->first();
            if (! $fresh || $fresh->status === 'paid') {
                return;
            }

            $fresh->update([
                'status' => 'paid',
                'payment_reference' => $paymentReference,
                'admin_notes' => $adminNotes,
                'processed_at' => now(),
            ]);

            // Mark all currently-available commissions for this affiliate as withdrawn
            // up to the withdrawal amount, oldest first.
            $remaining = (float) $fresh->amount;
            AffiliateCommission::query()
                ->where('affiliate_id', $fresh->affiliate_id)
                ->where('status', 'available')
                ->orderBy('earned_at')
                ->each(function (AffiliateCommission $c) use (&$remaining, $fresh): bool {
                    if ($remaining <= 0) {
                        return false;
                    }
                    $c->update(['status' => 'withdrawn', 'withdrawal_id' => $fresh->id]);
                    $remaining -= (float) $c->amount;

                    return true;
                });

            $this->safeMail($fresh->affiliate->user?->email, new AffiliateWithdrawalPaid($fresh));
        });
    }

    /**
     * Admin rejects a withdrawal; the held amount is returned to available.
     */
    public function rejectWithdrawal(AffiliateWithdrawal $withdrawal, string $reason): void
    {
        DB::transaction(function () use ($withdrawal, $reason): void {
            $fresh = AffiliateWithdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->first();
            if (! $fresh || in_array($fresh->status, ['paid', 'rejected'], true)) {
                return;
            }

            $affiliate = Affiliate::query()->whereKey($fresh->affiliate_id)->lockForUpdate()->first();

            $fresh->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'processed_at' => now(),
            ]);

            $affiliate->update([
                'available_balance' => (string) ((float) $affiliate->available_balance + (float) $fresh->amount),
                'total_withdrawn' => (string) ((float) $affiliate->total_withdrawn - (float) $fresh->amount),
            ]);

            $this->safeMail($fresh->affiliate->user?->email, new AffiliateWithdrawalRejected($fresh, $reason));
        });
    }

    public function suspend(Affiliate $affiliate, string $reason, ?User $admin = null): void
    {
        $affiliate->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);
        $this->safeMail($affiliate->user?->email, new AffiliateSuspended($affiliate, $reason, false));
    }

    public function reactivate(Affiliate $affiliate, ?User $admin = null): void
    {
        $affiliate->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);
        $this->safeMail($affiliate->user?->email, new AffiliateSuspended($affiliate, null, true));
    }

    public function adjustBalance(Affiliate $affiliate, string $type, float $amount, string $reason, ?User $admin = null): void
    {
        DB::transaction(function () use ($affiliate, $type, $amount, $reason, $admin): void {
            $fresh = Affiliate::query()->whereKey($affiliate->id)->lockForUpdate()->first();

            AffiliateBalanceAdjustment::create([
                'affiliate_id' => $fresh->id,
                'admin_user_id' => $admin?->id ?? auth()->id(),
                'type' => $type,
                'amount' => (string) $amount,
                'reason' => $reason,
            ]);

            $delta = $type === 'credit' ? $amount : -$amount;
            $fresh->update([
                'available_balance' => (string) ((float) $fresh->available_balance + $delta),
                'total_earned' => $type === 'credit'
                    ? (string) ((float) $fresh->total_earned + $amount)
                    : $fresh->total_earned,
            ]);
        });
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

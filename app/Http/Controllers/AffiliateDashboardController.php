<?php

namespace App\Http\Controllers;

use App\Models\AffiliateCommission;
use App\Models\AffiliateWithdrawal;
use App\Models\Order;
use App\Models\Setting;
use App\Services\AffiliateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateDashboardController extends Controller
{
    public function __construct(private readonly AffiliateService $affiliates)
    {
    }

    public function home(): Response
    {
        $affiliate = Auth::user()->affiliate;

        $recentCommissions = $affiliate->commissions()
            ->with('order:id,reference,placed_at')
            ->latest('earned_at')
            ->limit(5)
            ->get()
            ->map(fn (AffiliateCommission $c): array => $this->serializeCommission($c));

        $totalReferrals = Order::query()
            ->where('affiliate_id', $affiliate->id)
            ->where('payment_status', 'paid')
            ->count();

        return Inertia::render('Affiliate/Dashboard/Home', [
            'affiliate' => $this->serializeAffiliate($affiliate),
            'recentCommissions' => $recentCommissions,
            'totalReferrals' => $totalReferrals,
            'shareUrl' => url('/'),
        ]);
    }

    public function orders(): Response
    {
        $affiliate = Auth::user()->affiliate;

        $orders = Order::query()
            ->where('affiliate_id', $affiliate->id)
            ->latest('placed_at')
            ->paginate(20)
            ->through(fn (Order $order): array => [
                'reference' => $order->reference,
                'placed_at' => $order->placed_at?->toIso8601String(),
                'total' => (float) $order->total,
                'discount' => (float) $order->affiliate_discount_total,
                'commission' => (float) $order->affiliate_commission_total,
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
            ]);

        return Inertia::render('Affiliate/Dashboard/Orders', [
            'affiliate' => $this->serializeAffiliate($affiliate),
            'orders' => $orders,
        ]);
    }

    public function commissions(Request $request): Response
    {
        $affiliate = Auth::user()->affiliate;

        $query = $affiliate->commissions()->with('order:id,reference,placed_at')->latest('earned_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        $commissions = $query->paginate(20)->through(fn (AffiliateCommission $c): array => $this->serializeCommission($c));

        return Inertia::render('Affiliate/Dashboard/Commissions', [
            'affiliate' => $this->serializeAffiliate($affiliate),
            'commissions' => $commissions,
            'filter' => $status ?: null,
        ]);
    }

    public function withdrawals(): Response
    {
        $affiliate = Auth::user()->affiliate;

        $withdrawals = $affiliate->withdrawals()
            ->latest('requested_at')
            ->paginate(20)
            ->through(fn (AffiliateWithdrawal $w): array => [
                'reference' => $w->reference,
                'amount' => (float) $w->amount,
                'fee' => (float) $w->fee,
                'net_amount' => (float) $w->net_amount,
                'status' => $w->status,
                'requested_at' => $w->requested_at?->toIso8601String(),
                'processed_at' => $w->processed_at?->toIso8601String(),
                'payment_reference' => $w->payment_reference,
                'admin_notes' => $w->admin_notes,
                'bank_snapshot' => $w->bank_snapshot,
            ]);

        return Inertia::render('Affiliate/Dashboard/Withdrawals', [
            'affiliate' => $this->serializeAffiliate($affiliate),
            'withdrawals' => $withdrawals,
        ]);
    }

    public function newWithdrawal(): Response
    {
        $affiliate = Auth::user()->affiliate;

        return Inertia::render('Affiliate/Dashboard/NewWithdrawal', [
            'affiliate' => $this->serializeAffiliate($affiliate),
            'minimum' => (float) Setting::get('affiliate_minimum_withdrawal', '5000'),
            'fee' => (float) Setting::get('affiliate_withdrawal_fee', '100'),
            'hasPending' => $affiliate->withdrawals()
                ->whereIn('status', ['pending', 'approved'])
                ->exists(),
        ]);
    }

    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $affiliate = Auth::user()->affiliate;
        $this->affiliates->requestWithdrawal($affiliate, (float) $data['amount']);

        return redirect()->route('affiliate.dashboard.withdrawals')
            ->with('success', 'Withdrawal request submitted. We process payouts within 1–3 business days.');
    }

    public function bankDetails(): Response
    {
        $affiliate = Auth::user()->affiliate;

        return Inertia::render('Affiliate/Dashboard/BankDetails', [
            'affiliate' => $this->serializeAffiliate($affiliate),
        ]);
    }

    public function updateBankDetails(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'regex:/^\d{10}$/'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ]);

        $affiliate = Auth::user()->affiliate;
        $affiliate->update($data);

        return back()->with('success', 'Bank details updated.');
    }

    public function profile(): Response
    {
        $affiliate = Auth::user()->affiliate;

        return Inertia::render('Affiliate/Dashboard/Profile', [
            'affiliate' => $this->serializeAffiliate($affiliate),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'social_handles' => ['nullable', 'array'],
            'social_handles.instagram' => ['nullable', 'string', 'max:100'],
            'social_handles.twitter' => ['nullable', 'string', 'max:100'],
            'social_handles.tiktok' => ['nullable', 'string', 'max:100'],
            'social_handles.facebook' => ['nullable', 'string', 'max:100'],
        ]);

        $affiliate = Auth::user()->affiliate;
        $affiliate->update([
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'social_handles' => $data['social_handles'] ?? [],
        ]);

        return back()->with('success', 'Profile updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAffiliate(\App\Models\Affiliate $a): array
    {
        return [
            'code' => $a->code,
            'status' => $a->status,
            'name' => $a->user->name,
            'email' => $a->user->email,
            'phone' => $a->user->phone,
            'whatsapp_number' => $a->whatsapp_number,
            'social_handles' => $a->social_handles ?? [],
            'bank_name' => $a->bank_name,
            'bank_account_number' => $a->bank_account_number,
            'bank_account_name' => $a->bank_account_name,
            'pending_balance' => (float) $a->pending_balance,
            'available_balance' => (float) $a->available_balance,
            'total_earned' => (float) $a->total_earned,
            'total_withdrawn' => (float) $a->total_withdrawn,
            'joined_at' => $a->joined_at?->toIso8601String(),
            'has_bank_details' => filled($a->bank_name) && filled($a->bank_account_number),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCommission(AffiliateCommission $c): array
    {
        return [
            'id' => $c->id,
            'order_reference' => $c->order?->reference,
            'amount' => (float) $c->amount,
            'status' => $c->status,
            'earned_at' => $c->earned_at?->toIso8601String(),
            'available_at' => $c->available_at?->toIso8601String(),
            'reversed_at' => $c->reversed_at?->toIso8601String(),
            'reversal_reason' => $c->reversal_reason,
        ];
    }
}

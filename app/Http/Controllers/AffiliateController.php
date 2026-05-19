<?php

namespace App\Http\Controllers;

use App\Http\Requests\AffiliateSignupRequest;
use App\Models\Setting;
use App\Services\AffiliateService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AffiliateController extends Controller
{
    public function __construct(
        private readonly AffiliateService $affiliates,
        private readonly CartService $cart,
    ) {
    }

    public function landing(): Response
    {
        return Inertia::render('Affiliate/Landing', [
            'commissionHoldDays' => (int) Setting::get('affiliate_commission_hold_days', '7'),
            'minimumWithdrawal' => (float) Setting::get('affiliate_minimum_withdrawal', '5000'),
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Affiliate/Terms', [
            'commissionHoldDays' => (int) Setting::get('affiliate_commission_hold_days', '7'),
            'fallbackDays' => (int) Setting::get('affiliate_commission_hold_fallback_days', '30'),
            'minimumWithdrawal' => (float) Setting::get('affiliate_minimum_withdrawal', '5000'),
            'withdrawalFee' => (float) Setting::get('affiliate_withdrawal_fee', '100'),
        ]);
    }

    public function signupForm(): Response
    {
        return Inertia::render('Affiliate/Signup');
    }

    public function signup(AffiliateSignupRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $affiliate = $this->affiliates->signup($data);

        Auth::login($affiliate->user);
        $request->session()->regenerate();

        if ($affiliate->isSuspended()) {
            return redirect()->route('affiliate.pending');
        }

        return redirect()->route('affiliate.dashboard')
            ->with('success', "Welcome! Your affiliate code is {$affiliate->code}.");
    }

    public function pending(): Response
    {
        return Inertia::render('Affiliate/Pending');
    }

    public function suspended(): Response
    {
        $affiliate = Auth::user()?->affiliate;

        return Inertia::render('Affiliate/Suspended', [
            'reason' => $affiliate?->suspension_reason,
        ]);
    }

    public function applyCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $affiliate = $this->affiliates->findByCode($data['code']);
        if (! $affiliate) {
            return back()->withErrors(['code' => 'That affiliate code is not valid.']);
        }

        $this->cart->applyAffiliateCode($affiliate->code);
        $this->cart->repriceAll();

        return back()->with('success', "Code {$affiliate->code} applied — affiliate prices are now active.");
    }

    public function removeCode(): RedirectResponse
    {
        $this->cart->clearAffiliateCode();
        $this->cart->repriceAll();

        return back()->with('success', 'Affiliate code removed.');
    }
}

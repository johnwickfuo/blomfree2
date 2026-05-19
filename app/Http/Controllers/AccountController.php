<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Order;
use App\Services\InstallmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AccountController extends Controller
{
    public function __construct(private readonly InstallmentService $installments)
    {
    }

    public function dashboard(): InertiaResponse
    {
        $user = Auth::user();
        $plans = $user->installmentPlans()->latest()->get();
        $active = $plans->whereIn('status', ['active', 'awaiting_down_payment']);

        $nextDue = $this->nextDueAcrossPlans($active);

        return Inertia::render('Account/Dashboard', [
            'stats' => [
                'active_count' => $active->count(),
                'lifetime_paid' => (float) $plans->sum(fn ($p): float => (float) $p->amount_paid),
                'outstanding' => (float) $active->sum(fn ($p): float => $p->remainingAmount()),
                'next_due' => $nextDue,
            ],
            'recentPlans' => $plans->take(5)->map(fn (InstallmentPlan $p): array => $this->serializePlan($p))->values(),
        ]);
    }

    public function installmentsIndex(Request $request): InertiaResponse
    {
        $user = Auth::user();
        $status = $request->string('status')->toString();

        $plans = $user->installmentPlans()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->through(fn (InstallmentPlan $p) => $this->serializePlan($p));

        return Inertia::render('Account/Installments/Index', [
            'plans' => $plans,
            'filter' => $status ?: null,
        ]);
    }

    public function installmentShow(InstallmentPlan $plan): InertiaResponse
    {
        abort_unless($plan->user_id === Auth::id(), 403);

        $plan->load(['installable', 'payments']);

        return Inertia::render('Account/Installments/Show', [
            'plan' => array_merge($this->serializePlan($plan), [
                'payments' => $plan->payments->map(fn (InstallmentPayment $p): array => [
                    'reference' => $p->reference,
                    'amount' => (float) $p->amount,
                    'payment_gateway' => $p->payment_gateway,
                    'payment_reference' => $p->payment_reference,
                    'payment_status' => $p->payment_status,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                    'is_down_payment' => $p->is_down_payment,
                ]),
                'suggested_schedule' => $plan->suggested_schedule ?? [],
                'admin_notes' => $plan->admin_notes,
            ]),
        ]);
    }

    public function cancelInstallment(InstallmentPlan $plan): RedirectResponse
    {
        abort_unless($plan->user_id === Auth::id(), 403);

        $this->installments->customerCancel($plan);

        return redirect()->route('account.installments.show', ['plan' => $plan->reference])
            ->with('success', 'Your plan has been cancelled. We will process your refund within 7–14 business days.');
    }

    public function paymentReceipt(InstallmentPlan $plan, InstallmentPayment $payment): Response
    {
        abort_unless($plan->user_id === Auth::id(), 403);
        abort_unless($payment->installment_plan_id === $plan->id, 404);

        $pdf = Pdf::loadView('pdf.installment-payment-receipt', [
            'plan' => $plan,
            'payment' => $payment,
        ]);

        return $pdf->download($payment->reference.'.pdf');
    }

    public function planStatement(InstallmentPlan $plan): Response
    {
        abort_unless($plan->user_id === Auth::id(), 403);

        $plan->load('payments');
        $pdf = Pdf::loadView('pdf.installment-statement', [
            'plan' => $plan,
        ]);

        return $pdf->download($plan->reference.'-statement.pdf');
    }

    public function profile(): InertiaResponse
    {
        return Inertia::render('Account/Profile', [
            'user' => $this->serializeUser(Auth::user()),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^(\+?234|0)[789]\d{9}$/'],
            'delivery_address' => ['nullable', 'string', 'max:1000'],
            'delivery_state' => ['nullable', 'string', 'max:100'],
            'delivery_lga' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update(['password' => Hash::make($request->input('password'))]);

        return back()->with('success', 'Password updated.');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = Auth::user();

        if ($user->activeInstallmentPlans()->exists()) {
            return back()->withErrors([
                'password' => 'You have active installment plans. Complete or cancel them before deactivating your account.',
            ]);
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deactivated.');
    }

    public function bank(): InertiaResponse
    {
        return Inertia::render('Account/Bank', [
            'user' => $this->serializeUser(Auth::user()),
        ]);
    }

    public function updateBank(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'regex:/^\d{10}$/'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update($data);

        return back()->with('success', 'Bank details updated.');
    }

    public function orders(): InertiaResponse
    {
        $user = Auth::user();
        $orders = Order::query()
            ->where('customer_email', $user->email)
            ->latest('placed_at')
            ->paginate(15)
            ->through(fn (Order $o): array => [
                'reference' => $o->reference,
                'placed_at' => $o->placed_at?->toIso8601String(),
                'total' => (float) $o->total,
                'order_status' => $o->order_status,
                'payment_status' => $o->payment_status,
            ]);

        return Inertia::render('Account/Orders', ['orders' => $orders]);
    }

    private function serializePlan(InstallmentPlan $p): array
    {
        return [
            'reference' => $p->reference,
            'installable_label' => $p->installable_label,
            'installable_type' => $p->installable_type,
            'subsidiary' => $p->subsidiary,
            'status' => $p->status,
            'total_amount' => (float) $p->total_amount,
            'amount_paid' => (float) $p->amount_paid,
            'remaining' => $p->remainingAmount(),
            'progress' => $p->progressPercentage(),
            'minimum_down_payment_amount' => (float) $p->minimum_down_payment_amount,
            'down_payment_paid' => $p->down_payment_paid,
            'deadline' => $p->deadline?->toDateString(),
            'forfeiture_percentage' => $p->forfeiture_percentage,
            'requested_at' => $p->requested_at?->toIso8601String(),
            'activated_at' => $p->activated_at?->toIso8601String(),
            'completed_at' => $p->completed_at?->toIso8601String(),
            'cancelled_at' => $p->cancelled_at?->toIso8601String(),
            'defaulted_at' => $p->defaulted_at?->toIso8601String(),
        ];
    }

    private function serializeUser($user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'delivery_address' => $user->delivery_address,
            'delivery_state' => $user->delivery_state,
            'delivery_lga' => $user->delivery_lga,
            'bank_name' => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_name' => $user->bank_account_name,
            'has_bank_details' => $user->hasBankDetails(),
        ];
    }

    private function nextDueAcrossPlans($activePlans): ?array
    {
        $next = null;
        foreach ($activePlans as $plan) {
            foreach ($plan->suggested_schedule ?? [] as $entry) {
                $due = $entry['due_date'] ?? null;
                if (! $due) {
                    continue;
                }
                if ($plan->amount_paid >= ($entry['suggested_amount'] ?? 0) && ($entry['paid'] ?? false)) {
                    continue;
                }
                if ($due >= now()->toDateString() && ($next === null || $due < $next['due_date'])) {
                    $next = [
                        'plan_reference' => $plan->reference,
                        'due_date' => $due,
                        'amount' => (float) ($entry['suggested_amount'] ?? 0),
                    ];
                }
            }
        }
        return $next;
    }
}

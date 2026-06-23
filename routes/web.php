<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliateDashboardController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Featured listings are populated as each subsidiary phase lands.
    return Inertia::render('Home', [
        'featured' => [
            'lands' => [],
            'animals' => [],
            'collections' => [],
            'gadgets' => [],
        ],
    ]);
})->name('home')->middleware('edge-cache');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about')->middleware('edge-cache');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
 * Public contact form + inquiry submission.
 */
Route::get('/contact', function () {
    return Inertia::render('Contact', [
        'subjects' => \App\Models\Inquiry::SUBJECTS,
        'whatsappNumber' => '2348103965317',
    ]);
})->name('contact.index')->middleware('edge-cache');

Route::post('/inquiries', [InquiryController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('inquiries.store');

/*
 * BLOMFREE Estates & Properties — land listings.
 */
Route::get('/lands', [LandController::class, 'index'])->name('lands.index')->middleware('edge-cache');
Route::get('/lands/{land}', [LandController::class, 'show'])->name('lands.show');
Route::get('/lands/{land}/inspect', [InspectionController::class, 'createForLand'])->name('inspections.create');

/*
 * BLOMFREE Kennel & Farm — animal listings.
 */
Route::get('/kennel-farm', [AnimalController::class, 'index'])->name('kennel-farm.index')->middleware('edge-cache');
Route::get('/kennel-farm/{animal}', [AnimalController::class, 'show'])->name('kennel-farm.show');
Route::get('/kennel-farm/{animal}/inspect', [InspectionController::class, 'createForAnimal'])->name('animal-inspections.create');

/*
 * Inspection booking system — shared across subsidiaries (Phase 3).
 */
Route::post('/inspections', [InspectionController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('inspections.store');
Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');

/*
 * BLOMFREE Collections — unisex clothing & accessories catalog.
 */
Route::get('/collections', [ProductController::class, 'collectionsIndex'])->name('collections.index')->middleware('edge-cache');
Route::get('/collections/{product}', [ProductController::class, 'collectionsShow'])->name('collections.show');

/*
 * BLOMFREE Gadgets & Accessories — devices catalog.
 */
Route::get('/gadgets', [ProductController::class, 'gadgetsIndex'])->name('gadgets.index')->middleware('edge-cache');
Route::get('/gadgets/{product}', [ProductController::class, 'gadgetsShow'])->name('gadgets.show');

/*
 * Session-based shopping cart (guest checkout — no user accounts).
 */
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('cart.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

/*
 * Affiliate code application — session-scoped, no auth required.
 */
Route::post('/cart/apply-affiliate', [AffiliateController::class, 'applyCode'])
    ->middleware('throttle:20,1')
    ->name('cart.apply-affiliate');
Route::delete('/cart/apply-affiliate', [AffiliateController::class, 'removeCode'])
    ->name('cart.remove-affiliate');

/*
 * Checkout & orders — guest, no auth required.
 */
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.place');
Route::get('/checkout/verify/{reference}', [CheckoutController::class, 'verify'])->name('checkout.verify');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/track', [OrderController::class, 'trackForm'])->name('orders.track');
Route::post('/track', [OrderController::class, 'trackLookup']);

/*
 * Payment gateway webhooks (CSRF-exempt — see bootstrap/app.php).
 */
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
Route::post('/webhooks/flutterwave', [WebhookController::class, 'flutterwave'])->name('webhooks.flutterwave');

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    return Inertia::render('Dashboard', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'isAdmin' => (bool) $user->is_admin,
        'isAffiliate' => (bool) $user->is_affiliate,
        'installmentCount' => $user->installmentPlans()->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

/*
 * BLOMFREE Affiliate Program — public marketing + signup, then auth + active
 * affiliate dashboard.
 */
Route::get('/affiliate', [AffiliateController::class, 'landing'])->name('affiliate.landing')->middleware('edge-cache');
Route::get('/affiliate/terms', [AffiliateController::class, 'terms'])->name('affiliate.terms')->middleware('edge-cache');
Route::middleware('guest')->group(function () {
    Route::get('/affiliate/signup', [AffiliateController::class, 'signupForm'])->name('affiliate.signup');
    Route::post('/affiliate/signup', [AffiliateController::class, 'signup'])
        ->middleware('throttle:3,1440');
});

Route::middleware('auth')->group(function () {
    Route::get('/affiliate/pending', [AffiliateController::class, 'pending'])->name('affiliate.pending');
    Route::get('/affiliate/suspended', [AffiliateController::class, 'suspended'])->name('affiliate.suspended');
});

Route::middleware(['auth', 'affiliate'])->prefix('affiliate/dashboard')->name('affiliate.')->group(function () {
    Route::get('/', [AffiliateDashboardController::class, 'home'])->name('dashboard');
    Route::get('/orders', [AffiliateDashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/commissions', [AffiliateDashboardController::class, 'commissions'])->name('dashboard.commissions');
    Route::get('/withdrawals', [AffiliateDashboardController::class, 'withdrawals'])->name('dashboard.withdrawals');
    Route::get('/withdrawals/new', [AffiliateDashboardController::class, 'newWithdrawal'])->name('dashboard.withdrawals.new');
    Route::post('/withdrawals', [AffiliateDashboardController::class, 'storeWithdrawal'])
        ->middleware('throttle:5,60')
        ->name('dashboard.withdrawals.store');
    Route::get('/bank-details', [AffiliateDashboardController::class, 'bankDetails'])->name('dashboard.bank-details');
    Route::patch('/bank-details', [AffiliateDashboardController::class, 'updateBankDetails'])->name('dashboard.bank-details.update');
    Route::get('/profile', [AffiliateDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::patch('/profile', [AffiliateDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
 * BLOMFREE Installments — public marketing + initiation flow.
 */
Route::get('/installments', [InstallmentController::class, 'landing'])->name('installments.landing')->middleware('edge-cache');
Route::get('/installments/terms', [InstallmentController::class, 'terms'])->name('installments.terms')->middleware('edge-cache');
Route::middleware('auth')->group(function () {
    Route::get('/installments/initiate', [InstallmentController::class, 'show'])->name('installments.initiate');
    Route::post('/installments/initiate', [InstallmentController::class, 'store'])
        ->middleware('throttle:10,60')
        ->name('installments.store');
    Route::post('/installments/{plan}/initiate-payment', [InstallmentController::class, 'initiatePayment'])
        ->middleware('throttle:30,60')
        ->name('installments.initiate-payment');
    Route::get('/installments/{plan}/verify-payment', [InstallmentController::class, 'verifyPayment'])
        ->name('installments.verify-payment');
});

/*
 * Customer dashboard at /account — overview, installments, profile, bank.
 */
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/installments', [AccountController::class, 'installmentsIndex'])->name('installments.index');
    Route::get('/installments/{plan}', [AccountController::class, 'installmentShow'])->name('installments.show');
    Route::post('/installments/{plan}/cancel', [AccountController::class, 'cancelInstallment'])->name('installments.cancel');
    Route::get('/installments/{plan}/payments/{payment}/receipt', [AccountController::class, 'paymentReceipt'])->name('installments.receipt');
    Route::get('/installments/{plan}/statement', [AccountController::class, 'planStatement'])->name('installments.statement');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::delete('/account', [AccountController::class, 'deactivate'])->name('deactivate');
    Route::get('/bank', [AccountController::class, 'bank'])->name('bank');
    Route::patch('/bank', [AccountController::class, 'updateBank'])->name('bank.update');
});

require __DIR__.'/auth.php';

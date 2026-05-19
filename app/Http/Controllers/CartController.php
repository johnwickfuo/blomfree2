<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Animal;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Short identifiers accepted in `cartable_type` request input, mapped to
     * their concrete model classes.
     *
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public const CARTABLE_TYPES = [
        'product_variant' => ProductVariant::class,
        'product' => Product::class,
        'animal' => Animal::class,
    ];

    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Cart/Index');
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $class = self::CARTABLE_TYPES[$data['cartable_type']];
        $cartable = $class::findOrFail($data['cartable_id']);

        $this->cart->add($cartable, (int) $data['quantity']);

        return back();
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->ensureOwned($cartItem);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cart->update($cartItem, (int) $data['quantity']);

        return back();
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->ensureOwned($cartItem);

        $this->cart->remove($cartItem);

        return back();
    }

    private function ensureOwned(CartItem $item): void
    {
        abort_unless($item->cart_id === $this->cart->getOrCreate()->id, 403);
    }
}

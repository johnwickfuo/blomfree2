<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString() ?: 'available';

        $query = Land::query()->with('media');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($state = $request->string('state')->toString()) {
            $query->where('state', $state);
        }

        if ($minPlots = $request->integer('min_plots')) {
            $query->where('number_of_plots', '>=', $minPlots);
        }

        $priceMin = $request->input('price_min');
        if ($priceMin !== null && $priceMin !== '') {
            $query->where('price_per_plot', '>=', (float) $priceMin);
        }

        $priceMax = $request->input('price_max');
        if ($priceMax !== null && $priceMax !== '') {
            $query->where('price_per_plot', '<=', (float) $priceMax);
        }

        if ($request->boolean('installment')) {
            $query->where('installment_available', true);
        }

        $lands = $query
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Lands/Index', [
            'lands' => $lands,
            'states' => Land::query()->select('state')->distinct()->orderBy('state')->pluck('state'),
            'filters' => [
                'state' => $request->input('state'),
                'min_plots' => $request->input('min_plots'),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'installment' => $request->boolean('installment'),
                'status' => $status,
            ],
        ]);
    }

    public function show(Land $land): Response
    {
        $land->load('media')->append('gallery_urls');

        $related = Land::query()
            ->with('media')
            ->where('state', $land->state)
            ->whereKeyNot($land->getKey())
            ->where('status', 'available')
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->limit(3)
            ->get();

        return Inertia::render('Lands/Show', [
            'land' => $land,
            'related' => $related,
            'installment' => $land->installmentAvailableForPurchase() ? [
                'enabled' => true,
                'down_payment_percentage' => $land->installment_minimum_down_payment_percentage,
                'maximum_length_months' => $land->installment_maximum_length_months,
                'initiate_url' => '/installments/initiate?type=land&id='.$land->id,
            ] : null,
        ]);
    }
}

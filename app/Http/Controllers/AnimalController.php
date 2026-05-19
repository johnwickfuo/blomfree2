<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class AnimalController extends Controller
{
    private const TAB_CATEGORIES = ['dog', 'cat', 'rabbit', 'grasscutter'];

    public function index(): Response
    {
        $animals = Animal::query()
            ->with('media')
            ->where(fn (Builder $query) => $query
                ->where('listing_type', 'pool')
                ->orWhere('availability', '!=', 'sold'))
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        $animalsByCategory = [];
        foreach (self::TAB_CATEGORIES as $category) {
            $animalsByCategory[$category] = ($animals->get($category) ?? collect())->values();
        }

        return Inertia::render('KennelFarm/Index', [
            'animalsByCategory' => $animalsByCategory,
        ]);
    }

    public function show(Animal $animal): Response
    {
        $animal->load('media')->append('gallery_urls');

        $related = Animal::query()
            ->with('media')
            ->where('category', $animal->category)
            ->whereKeyNot($animal->getKey())
            ->where(fn (Builder $query) => $query
                ->where('listing_type', 'pool')
                ->orWhere('availability', '!=', 'sold'))
            ->orderByDesc('is_featured')
            ->orderBy('order')
            ->limit(3)
            ->get();

        return Inertia::render('KennelFarm/Show', [
            'animal' => $animal,
            'related' => $related,
        ]);
    }
}

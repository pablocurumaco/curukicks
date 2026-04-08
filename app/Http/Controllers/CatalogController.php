<?php

namespace App\Http\Controllers;

use App\Enums\SneakerStatus;
use App\Models\Sneaker;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Sneaker::query()->public()->available();

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        if ($request->filled('price_min')) {
            $query->where('sale_price_gt', '>=', (int) $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('sale_price_gt', '<=', (int) $request->price_max);
        }

        $sort = $request->get('sort', 'model_asc');
        match ($sort) {
            'price_asc' => $query->orderByRaw('sale_price_gt IS NULL, sale_price_gt ASC'),
            'price_desc' => $query->orderByRaw('sale_price_gt IS NULL, sale_price_gt DESC'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'size_asc' => $query->orderBy('size', 'asc'),
            default => $query->orderBy('model'),
        };

        $sneakers = $query->get();

        $brands = Sneaker::query()->public()->available()
            ->whereNotNull('brand')->distinct()->pluck('brand')->sort()->values();
        $sizes = Sneaker::query()->public()->available()
            ->distinct()->pluck('size')->sort()->values();

        return view('catalog.index', compact('sneakers', 'brands', 'sizes'));
    }

    public function show(Sneaker $sneaker)
    {
        if (! $sneaker->is_public || $sneaker->status !== SneakerStatus::Available) {
            abort(404);
        }

        $existingOffer = $sneaker->pendingOfferFrom(auth()->user());

        return view('catalog.show', compact('sneaker', 'existingOffer'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\SneakerStatus;
use App\Models\Offer;
use App\Models\Sneaker;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function store(Request $request, Sneaker $sneaker)
    {
        if (! $request->user()->hasRole('comprador')) {
            abort(403);
        }

        if (! $sneaker->is_public || $sneaker->status !== SneakerStatus::Available) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $existing = $sneaker->pendingOfferFrom($request->user());

        if ($existing) {
            return back()->with('error', 'Ya tenés una oferta pendiente para este par.');
        }

        Offer::create([
            'user_id' => $request->user()->id,
            'sneaker_id' => $sneaker->id,
            'amount' => $validated['amount'],
        ]);

        return back()->with('success', 'Oferta enviada correctamente.');
    }

    public function index(Request $request)
    {
        if (! $request->user()->hasRole('comprador')) {
            abort(403);
        }

        $offers = $request->user()
            ->offers()
            ->with('sneaker')
            ->latest()
            ->get();

        return view('catalog.mis-ofertas', compact('offers'));
    }

    public function destroy(Request $request, Offer $offer)
    {
        if ($offer->user_id !== $request->user()->id || ! $offer->isPending()) {
            abort(403);
        }

        $offer->delete();

        return back()->with('success', 'Oferta cancelada.');
    }
}

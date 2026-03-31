<?php

namespace App\Http\Controllers;

use App\Enums\SneakerStatus;
use App\Models\Sneaker;

class CatalogController extends Controller
{
    public function index()
    {
        $sneakers = Sneaker::query()
            ->public()
            ->available()
            ->orderBy('model')
            ->get();

        return view('catalog.index', compact('sneakers'));
    }

    public function show(Sneaker $sneaker)
    {
        if (! $sneaker->is_public || $sneaker->status !== SneakerStatus::Available) {
            abort(404);
        }

        return view('catalog.show', compact('sneaker'));
    }
}

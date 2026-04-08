<?php

namespace App\Providers;

use App\Enums\OfferStatus;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.catalog', function ($view) {
            $pendingOffersCount = 0;

            if (auth()->check() && auth()->user()->hasRole('comprador')) {
                $pendingOffersCount = auth()->user()->offers()->where('status', OfferStatus::Pending)->count();
            }

            $view->with('pendingOffersCount', $pendingOffersCount);
        });
    }
}

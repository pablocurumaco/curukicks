<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OfferController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/sneaker/{sneaker:slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/sneaker/{sneaker:slug}/ofertar', [OfferController::class, 'store'])->name('offers.store');
    Route::get('/mis-ofertas', [OfferController::class, 'index'])->name('offers.index');
    Route::delete('/oferta/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');
});

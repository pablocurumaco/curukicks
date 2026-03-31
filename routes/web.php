<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/sneaker/{sneaker:slug}', [CatalogController::class, 'show'])->name('catalog.show');

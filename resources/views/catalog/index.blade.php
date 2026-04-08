@extends('layouts.catalog')

@section('title', 'CuruKicks — Sneakers en Venta')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Sneakers en Venta</h1>
        <p class="text-neutral-400 mt-2">{{ $sneakers->count() }} pares disponibles</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('catalog.index') }}" class="mb-8 bg-neutral-900 rounded-xl border border-neutral-800 p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <select name="brand" class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-orange-500">
                <option value="">Marca</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}" {{ request('brand') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                @endforeach
            </select>

            <select name="condition" class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-orange-500">
                <option value="">Estado</option>
                <option value="DS" {{ request('condition') === 'DS' ? 'selected' : '' }}>Deadstock</option>
                <option value="Used" {{ request('condition') === 'Used' ? 'selected' : '' }}>Usado</option>
            </select>

            <select name="size" class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-orange-500">
                <option value="">Talla</option>
                @foreach($sizes as $size)
                    <option value="{{ $size }}" {{ request('size') === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>

            <input type="number" name="price_min" placeholder="Precio mín" value="{{ request('price_min') }}"
                   class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-orange-500">

            <input type="number" name="price_max" placeholder="Precio máx" value="{{ request('price_max') }}"
                   class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-orange-500">

            <select name="sort" class="bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-orange-500">
                <option value="model_asc" {{ request('sort', 'model_asc') === 'model_asc' ? 'selected' : '' }}>Nombre A-Z</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Precio ↑</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Precio ↓</option>
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Más recientes</option>
                <option value="size_asc" {{ request('sort') === 'size_asc' ? 'selected' : '' }}>Talla ↑</option>
            </select>
        </div>
        <div class="flex gap-3 mt-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['brand', 'condition', 'size', 'price_min', 'price_max', 'sort']))
                <a href="{{ route('catalog.index') }}" class="text-sm text-neutral-500 hover:text-orange-400 transition-colors py-2">
                    Limpiar filtros
                </a>
            @endif
        </div>
    </form>

    @if($sneakers->isEmpty())
        <div class="text-center py-20">
            <p class="text-neutral-500 text-lg">No hay sneakers publicados todavía.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($sneakers as $sneaker)
                <a href="{{ route('catalog.show', $sneaker) }}"
                   class="group bg-neutral-900 rounded-2xl overflow-hidden border border-neutral-800 hover:border-orange-500/50 transition-all duration-200 hover:shadow-lg hover:shadow-orange-500/5">

                    {{-- Photo --}}
                    <div class="aspect-square bg-neutral-800 flex items-center justify-center overflow-hidden">
                        @if($sneaker->photos && count($sneaker->photos) > 0)
                            <img src="{{ Storage::disk('public')->url($sneaker->photos[0]) }}"
                                 alt="{{ $sneaker->model }} {{ $sneaker->colorway }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                        @else
                            <svg class="w-16 h-16 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="font-semibold text-white group-hover:text-orange-400 transition-colors truncate">
                            {{ $sneaker->model }}
                        </h3>
                        <p class="text-sm text-neutral-400 truncate">{{ $sneaker->colorway }}</p>

                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-neutral-500">
                                <span>Talla {{ $sneaker->size }}</span>
                                <span>&middot;</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sneaker->condition->value === 'DS' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                                    {{ $sneaker->condition->value }}
                                </span>
                            </div>
                        </div>

                        @if($sneaker->sale_price_gt)
                            <p class="mt-3 text-xl font-bold text-orange-400">
                                Q{{ number_format($sneaker->sale_price_gt) }}
                            </p>
                        @else
                            <p class="mt-3 text-sm text-neutral-600">Consultar precio</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection

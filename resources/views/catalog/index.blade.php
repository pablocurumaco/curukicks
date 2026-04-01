@extends('layouts.catalog')

@section('title', 'CuruKicks — Sneakers en Venta')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Sneakers en Venta</h1>
        <p class="text-zinc-400 mt-2">{{ $sneakers->count() }} pares disponibles</p>
    </div>

    @if($sneakers->isEmpty())
        <div class="text-center py-20">
            <p class="text-zinc-500 text-lg">No hay sneakers publicados todavia.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($sneakers as $sneaker)
                <a href="{{ route('catalog.show', $sneaker) }}"
                   class="group bg-zinc-900 rounded-2xl overflow-hidden border border-zinc-800 hover:border-amber-400/50 transition-all duration-200 hover:shadow-lg hover:shadow-amber-400/5">

                    {{-- Photo --}}
                    <div class="aspect-square bg-zinc-800 flex items-center justify-center overflow-hidden">
                        @if($sneaker->photos && count($sneaker->photos) > 0)
                            <img src="{{ Storage::disk('public')->url($sneaker->photos[0]) }}"
                                 alt="{{ $sneaker->model }} {{ $sneaker->colorway }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                        @else
                            <svg class="w-16 h-16 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="font-semibold text-white group-hover:text-amber-400 transition-colors truncate">
                            {{ $sneaker->model }}
                        </h3>
                        <p class="text-sm text-zinc-400 truncate">{{ $sneaker->colorway }}</p>

                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <span>Talla {{ $sneaker->size }}</span>
                                <span>&middot;</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sneaker->condition->value === 'DS' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                                    {{ $sneaker->condition->value }}
                                </span>
                            </div>
                        </div>

                        @if($sneaker->sale_price_gt)
                            <p class="mt-3 text-xl font-bold text-amber-400">
                                Q{{ number_format($sneaker->sale_price_gt) }}
                            </p>
                        @else
                            <p class="mt-3 text-sm text-zinc-600">Consultar precio</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection

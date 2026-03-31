@extends('layouts.catalog')

@section('title', $sneaker->model . ' ' . $sneaker->colorway . ' — CuruKicks')
@section('description', $sneaker->model . ' ' . $sneaker->colorway . ' talla ' . $sneaker->size . '. ' . ($sneaker->condition->value === 'DS' ? 'Deadstock/Nuevo.' : 'Usado.'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('catalog.index') }}" class="text-sm text-zinc-500 hover:text-amber-400 transition-colors">
            &larr; Volver al catalogo
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Photo --}}
        <div class="aspect-square bg-zinc-900 rounded-2xl border border-zinc-800 flex items-center justify-center">
            <svg class="w-24 h-24 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>

        {{-- Details --}}
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">{{ $sneaker->model }}</h1>
            <p class="text-xl text-zinc-400 mt-1">{{ $sneaker->colorway }}</p>

            @if($sneaker->style_code)
                <p class="mt-2 text-sm text-zinc-500 font-mono">{{ $sneaker->style_code }}</p>
            @endif

            @if($sneaker->sale_price_gt)
                <p class="mt-6 text-4xl font-extrabold text-amber-400">
                    Q{{ number_format($sneaker->sale_price_gt) }}
                </p>
            @else
                <p class="mt-6 text-lg text-zinc-500">Consultar precio</p>
            @endif

            <div class="mt-8 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                        <p class="text-xs text-zinc-500 uppercase tracking-wider">Talla</p>
                        <p class="text-lg font-semibold mt-1">{{ $sneaker->size }}</p>
                    </div>
                    <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                        <p class="text-xs text-zinc-500 uppercase tracking-wider">Estado</p>
                        <p class="text-lg font-semibold mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $sneaker->condition->value === 'DS' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                                {{ $sneaker->condition->value === 'DS' ? 'Deadstock (Nuevo)' : 'Usado' }}
                            </span>
                        </p>
                    </div>
                    <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                        <p class="text-xs text-zinc-500 uppercase tracking-wider">Caja</p>
                        <p class="text-lg font-semibold mt-1">{{ $sneaker->has_box ? 'Si' : 'No' }}</p>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="mt-10">
                <p class="text-sm text-zinc-500">
                    Interesado? Escribime por WhatsApp para negociar.
                </p>
            </div>
        </div>
    </div>
@endsection

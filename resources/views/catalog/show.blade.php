@extends('layouts.catalog')

@section('title', $sneaker->model . ' ' . $sneaker->colorway . ' — CuruKicks')
@section('description', $sneaker->model . ' ' . $sneaker->colorway . ' talla ' . $sneaker->size . '. ' . ($sneaker->condition->value === 'DS' ? 'Deadstock/Nuevo.' : 'Usado.'))

@section('content')
    @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 rounded-lg p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-900/50 border border-red-700 text-red-300 rounded-lg p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('catalog.index') }}" class="text-sm text-neutral-500 hover:text-orange-400 transition-colors">
            &larr; Volver al catálogo
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Photos --}}
        <div class="space-y-3">
            @if($sneaker->photos && count($sneaker->photos) > 0)
                <div class="aspect-square bg-neutral-900 rounded-2xl border border-neutral-800 overflow-hidden">
                    <img src="{{ Storage::disk('public')->url($sneaker->photos[0]) }}"
                         alt="{{ $sneaker->model }} {{ $sneaker->colorway }}"
                         class="w-full h-full object-cover">
                </div>

                @if(count($sneaker->photos) > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(array_slice($sneaker->photos, 1) as $photo)
                            <div class="aspect-square bg-neutral-900 rounded-lg border border-neutral-800 overflow-hidden">
                                <img src="{{ Storage::disk('public')->url($photo) }}"
                                     alt="{{ $sneaker->model }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="aspect-square bg-neutral-900 rounded-2xl border border-neutral-800 flex items-center justify-center">
                    <svg class="w-24 h-24 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">{{ $sneaker->model }}</h1>
            <p class="text-xl text-neutral-400 mt-1">{{ $sneaker->colorway }}</p>

            @if($sneaker->style_code)
                <p class="mt-2 text-sm text-neutral-500 font-mono">{{ $sneaker->style_code }}</p>
            @endif

            @if($sneaker->sale_price_gt)
                <p class="mt-6 text-4xl font-extrabold text-orange-400">
                    Q{{ number_format($sneaker->sale_price_gt) }}
                </p>
            @else
                <p class="mt-6 text-lg text-neutral-500">Consultar precio</p>
            @endif

            <div class="mt-8 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
                        <p class="text-xs text-neutral-500 uppercase tracking-wider">Talla</p>
                        <p class="text-lg font-semibold mt-1">{{ $sneaker->size }}</p>
                    </div>
                    <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
                        <p class="text-xs text-neutral-500 uppercase tracking-wider">Estado</p>
                        <p class="text-lg font-semibold mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $sneaker->condition->value === 'DS' ? 'bg-green-900/50 text-green-400' : 'bg-yellow-900/50 text-yellow-400' }}">
                                {{ $sneaker->condition->value === 'DS' ? 'Deadstock (Nuevo)' : 'Usado' }}
                            </span>
                        </p>
                    </div>
                    <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
                        <p class="text-xs text-neutral-500 uppercase tracking-wider">Caja</p>
                        <p class="text-lg font-semibold mt-1">{{ $sneaker->has_box ? 'Sí' : 'No' }}</p>
                    </div>
                    @if($sneaker->stockx_url)
                        <a href="{{ $sneaker->stockx_url }}" target="_blank" rel="noopener noreferrer"
                           class="bg-[#006340] hover:bg-[#00754a] rounded-xl p-4 border border-[#007a4d] flex items-center justify-center transition-colors">
                            <span class="text-lg font-bold text-white tracking-wide">StockX</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Offer Section --}}
            @auth
                @if(Auth::user()->hasRole('comprador'))
                    <div class="mt-8">
                        @if($existingOffer)
                            <div class="bg-neutral-900 rounded-xl p-5 border border-orange-500/30">
                                <p class="text-sm text-neutral-400">Tu oferta pendiente</p>
                                <p class="text-2xl font-bold text-orange-400 mt-1">Q{{ number_format($existingOffer->amount) }}</p>
                                <form method="POST" action="{{ route('offers.destroy', $existingOffer) }}" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                                        Cancelar oferta
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('offers.store', $sneaker) }}" class="bg-neutral-900 rounded-xl p-5 border border-neutral-800">
                                @csrf
                                <label for="amount" class="block text-sm text-neutral-400 mb-2">Hacé tu oferta (Q)</label>
                                <div class="flex gap-3">
                                    <input type="number" name="amount" id="amount" min="1"
                                           placeholder="Ej: 1500" required
                                           value="{{ old('amount') }}"
                                           class="flex-1 bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-2.5 text-white placeholder-neutral-500 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                                        Ofertar
                                    </button>
                                </div>
                                @error('amount')
                                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </form>
                        @endif
                    </div>
                @endif
            @else
                <div class="mt-8">
                    <a href="{{ route('login') }}" class="text-sm text-neutral-500 hover:text-orange-400 transition-colors">
                        Iniciá sesión para hacer una oferta &rarr;
                    </a>
                </div>
            @endauth
        </div>
    </div>
@endsection

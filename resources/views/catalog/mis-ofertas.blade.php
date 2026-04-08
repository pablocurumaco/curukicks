@extends('layouts.catalog')

@section('title', 'Mis Ofertas — CuruKicks')

@section('content')
    @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 rounded-lg p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Mis Ofertas</h1>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
            <p class="text-xs text-neutral-500 uppercase tracking-wider">Total ofertas</p>
            <p class="text-2xl font-bold mt-1">{{ $offers->count() }}</p>
        </div>
        <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
            <p class="text-xs text-neutral-500 uppercase tracking-wider">Pendientes</p>
            <p class="text-2xl font-bold mt-1 text-yellow-400">{{ $offers->where('status', \App\Enums\OfferStatus::Pending)->count() }}</p>
        </div>
        <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
            <p class="text-xs text-neutral-500 uppercase tracking-wider">Aceptadas</p>
            <p class="text-2xl font-bold mt-1 text-green-400">{{ $offers->where('status', \App\Enums\OfferStatus::Accepted)->count() }}</p>
        </div>
        <div class="bg-neutral-900 rounded-xl p-4 border border-neutral-800">
            <p class="text-xs text-neutral-500 uppercase tracking-wider">Valor total ofertado</p>
            <p class="text-2xl font-bold mt-1 text-orange-400">Q{{ number_format($offers->sum('amount')) }}</p>
        </div>
    </div>

    @if($offers->isEmpty())
        <div class="text-center py-20">
            <p class="text-neutral-500 text-lg">No has hecho ofertas todavía.</p>
            <a href="{{ route('catalog.index') }}" class="text-orange-400 hover:text-orange-300 text-sm mt-2 inline-block">
                Explorar catálogo &rarr;
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($offers as $offer)
                <div class="bg-neutral-900 rounded-xl border border-neutral-800 p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    {{-- Sneaker Thumbnail --}}
                    <a href="{{ route('catalog.show', $offer->sneaker) }}" class="shrink-0">
                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-neutral-800">
                            @if($offer->sneaker->photos && count($offer->sneaker->photos) > 0)
                                <img src="{{ Storage::disk('public')->url($offer->sneaker->photos[0]) }}"
                                     alt="{{ $offer->sneaker->model }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-neutral-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </a>

                    {{-- Sneaker Info --}}
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('catalog.show', $offer->sneaker) }}" class="font-semibold text-white hover:text-orange-400 transition-colors truncate block">
                            {{ $offer->sneaker->model }}
                        </a>
                        <p class="text-sm text-neutral-400 truncate">{{ $offer->sneaker->colorway }} &middot; Talla {{ $offer->sneaker->size }}</p>
                        <p class="text-xs text-neutral-600 mt-1">{{ $offer->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Prices --}}
                    <div class="text-right shrink-0">
                        @if($offer->sneaker->sale_price_gt)
                            <p class="text-xs text-neutral-500">Precio pedido</p>
                            <p class="text-sm text-neutral-400">Q{{ number_format($offer->sneaker->sale_price_gt) }}</p>
                        @endif
                        <p class="text-xs text-neutral-500 mt-1">Tu oferta</p>
                        <p class="text-lg font-bold text-orange-400">Q{{ number_format($offer->amount) }}</p>
                    </div>

                    {{-- Status + Actions --}}
                    <div class="text-right shrink-0">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            {{ match($offer->status) {
                                \App\Enums\OfferStatus::Pending => 'bg-yellow-900/50 text-yellow-400',
                                \App\Enums\OfferStatus::Accepted => 'bg-green-900/50 text-green-400',
                                \App\Enums\OfferStatus::Rejected => 'bg-red-900/50 text-red-400',
                            } }}">
                            {{ $offer->status->getLabel() }}
                        </span>
                        @if($offer->isPending())
                            <form method="POST" action="{{ route('offers.destroy', $offer) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">
                                    Cancelar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

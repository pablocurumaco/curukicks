@extends('layouts.catalog')

@section('title', 'Iniciar Sesión — CuruKicks')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8">Iniciar Sesión</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-400 mb-2">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full px-4 py-3 bg-zinc-900 border border-zinc-700 rounded-lg text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                    placeholder="tu@email.com"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-zinc-400 mb-2">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-3 bg-zinc-900 border border-zinc-700 rounded-lg text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-zinc-600 bg-zinc-900 text-amber-400 focus:ring-amber-400 focus:ring-offset-0"
                >
                <label for="remember" class="ml-2 text-sm text-zinc-400">Recordarme</label>
            </div>

            <button
                type="submit"
                class="w-full py-3 px-4 bg-amber-400 hover:bg-amber-500 text-zinc-900 font-semibold rounded-lg transition-colors"
            >
                Entrar
            </button>
        </form>

        <p class="text-center mt-6">
            <a href="{{ route('catalog.index') }}" class="text-sm text-zinc-500 hover:text-amber-400 transition-colors">
                &larr; Volver al catálogo
            </a>
        </p>
    </div>
</div>
@endsection

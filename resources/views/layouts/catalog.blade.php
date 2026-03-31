<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CuruKicks — Sneakers GT')</title>
    <meta name="description" content="@yield('description', 'Coleccion de sneakers en Guatemala. Jordan, Yeezy, New Balance, Adidas y mas.')">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-zinc-950 text-white font-sans antialiased">
    <header class="border-b border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 flex items-center justify-between">
            <a href="{{ route('catalog.index') }}" class="text-2xl font-extrabold tracking-tight">
                Curu<span class="text-amber-400">Kicks</span>
            </a>
            <div class="flex items-center gap-4">
                <p class="text-sm text-zinc-500 hidden sm:block">Sneakers GT</p>
                @auth
                    <span class="text-sm text-zinc-400">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-zinc-500 hover:text-amber-400 transition-colors">
                            Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-zinc-500 hover:text-amber-400 transition-colors">
                        Iniciar Sesión
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-zinc-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 text-center text-sm text-zinc-600">
            &copy; {{ date('Y') }} CuruKicks &mdash; kicks.curumakito.com
        </div>
    </footer>
</body>
</html>

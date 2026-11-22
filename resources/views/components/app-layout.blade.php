<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agra Kreasi PPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">
    <!-- Navbar Responsif -->
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo & Menu Desktop -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="font-bold text-indigo-700 text-lg tracking-tight">
                        AgraKreasi <span class="text-gray-800">PPC</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-5">
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Dashboard</a>
                        <a href="{{ route('pesanan.create') }}" class="text-sm font-semibold {{ request()->routeIs('pesanan.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Input Order</a>
                        <a href="{{ route('cutting.queue') }}" class="text-sm font-semibold {{ request()->routeIs('cutting.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Antrean Produksi</a>
                        <a href="{{ route('wip.scan') }}" class="text-sm font-semibold {{ request()->routeIs('wip.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Scan QR / WIP</a>
                        @if(auth()->user()->role === 'manajer')
                            <a href="{{ route('kapasitas.index') }}" class="text-sm font-semibold {{ request()->routeIs('kapasitas.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Atur Jam Cutting</a>
                        @endif
                    </div>
                </div>

                <!-- Info User Desktop -->
                <div class="hidden md:flex items-center gap-4">
                    <span class="text-xs text-gray-500 font-medium">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 bg-red-50 text-red-600 font-bold rounded hover:bg-red-100">Logout</button>
                    </form>
                </div>

                <!-- Tombol Hamburger (Khusus Layar HP) -->
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                            class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Dropdown Menu Mobile (HP) -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200 px-4 pt-2 pb-4 space-y-1 shadow-lg">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-md text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">Dashboard</a>
            <a href="{{ route('pesanan.create') }}" class="block px-3 py-2.5 rounded-md text-sm font-semibold {{ request()->routeIs('pesanan.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">Input Order</a>
            <a href="{{ route('cutting.queue') }}" class="block px-3 py-2.5 rounded-md text-sm font-semibold {{ request()->routeIs('cutting.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">Antrean Produksi</a>
            <a href="{{ route('wip.scan') }}" class="block px-3 py-2.5 rounded-md text-sm font-semibold {{ request()->routeIs('wip.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">Scan QR / WIP</a>
            @if(auth()->user()->role === 'manajer')
                <a href="{{ route('kapasitas.index') }}" class="block px-3 py-2.5 rounded-md text-sm font-semibold {{ request()->routeIs('kapasitas.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">Atur Jam Cutting</a>
            @endif

            <div class="border-t border-gray-200 pt-3 mt-2 flex items-center justify-between px-3">
                <span class="text-xs text-gray-500 font-semibold">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1.5 bg-red-600 text-white font-bold rounded">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Header Halaman -->
    @if(isset($header))
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <!-- Konten Utama (Tambahkan px-3 agar tidak mepet tepi layar HP) -->
    <main class="px-3 sm:px-0 pb-10">
        {{ $slot }}
    </main>
</body>
</html>
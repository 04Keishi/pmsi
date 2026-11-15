<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agra Kreasi PPC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <div class="flex items-center space-x-6">
                <span class="font-bold text-indigo-700 text-lg">AgraKreasi PPC</span>
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Dashboard</a>
                <a href="{{ route('pesanan.create') }}" class="text-sm font-semibold {{ request()->routeIs('pesanan.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">+ Input Order</a>
                <a href="{{ route('cutting.queue') }}" class="text-sm font-semibold {{ request()->routeIs('cutting.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Antrean Cutting</a>
                <a href="{{ route('wip.scan') }}" class="text-sm font-semibold {{ request()->routeIs('wip.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Scan QR / WIP</a>
                @if(auth()->user()->role === 'manajer')
                    <a href="{{ route('kapasitas.index') }}" class="text-sm font-semibold {{ request()->routeIs('kapasitas.*') ? 'text-indigo-600 border-b-2 border-indigo-600 py-5' : 'text-gray-600 hover:text-gray-900' }}">Atur Jam Cutting</a>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1.5 bg-red-50 text-red-600 font-bold rounded hover:bg-red-100">Logout</button>
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

    <!-- Konten Utama -->
    <main>
        {{ $slot }}
    </main>
</body>
</html>
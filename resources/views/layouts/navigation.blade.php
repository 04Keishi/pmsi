<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
<x-nav-link :href="route('pesanan.create')" :active="request()->routeIs('pesanan.*')">+ Input Order</x-nav-link>
<x-nav-link :href="route('cutting.queue')" :active="request()->routeIs('cutting.*')">Antrean Cutting</x-nav-link>
<x-nav-link :href="route('wip.scan')" :active="request()->routeIs('wip.*')">Scan QR / WIP</x-nav-link>
@if(auth()->user()->role === 'manajer')
    <x-nav-link :href="route('kapasitas.index')" :active="request()->routeIs('kapasitas.*')">Atur Jam Cutting</x-nav-link>
@endif
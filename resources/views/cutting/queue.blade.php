<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Papan Antrean Meja Cutting (2 Operator)</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg font-medium">{{ session('success') }}</div>
        @endif

        <!-- Banner Rekomendasi Smart Batching -->
        @if($batching->count() > 0)
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded shadow">
            <h3 class="font-bold text-blue-900">Rekomendasi Smart Batching (Hemat Waktu Gelar Kain):</h3>
            <ul class="list-disc ml-5 mt-1 text-sm text-blue-800">
                @foreach($batching as $b)
                    <li>Kain <strong>{{ $b->jenis_kain }}</strong> memiliki <strong>{{ $b->total_order }} SPK aktif</strong> (Total {{ $b->total_pcs }} pcs / {{ round($b->total_jam, 2) }} jam) — Gabungkan pemotongan sekaligus.</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Antrean Siap Potong -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4 text-green-700">1. Antrean Siap Potong (Buffer Ready)</h3>
            <div class="space-y-3">
                @forelse($siapPotong as $order)
                <div class="border rounded-lg p-4 flex flex-wrap justify-between items-center gap-4 {{ $order->is_rush_order ? 'border-red-400 bg-red-50' : 'bg-gray-50' }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-gray-800 text-white text-xs font-bold rounded">Prioritas #{{ $order->urutan_prioritas }}</span>
                            <span class="font-mono font-bold text-base">{{ $order->kode_spk }}</span>
                            @if($order->is_rush_order)
                                <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-bold rounded">RUSH ORDER</span>
                            @endif
                        </div>
                        <p class="font-semibold mt-1">{{ $order->nama_pelanggan }} — {{ $order->model_pakaian }}</p>
                        <p class="text-xs text-gray-600">Kain: <strong>{{ $order->jenis_kain }}</strong> | Qty: <strong>{{ $order->jumlah_pcs }} pcs</strong> | Est. Cutting: <strong>{{ $order->estimasi_jam_cutting }} jam</strong> | Deadline: <strong>{{ $order->tanggal_deadline }}</strong></p>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('cutting.prioritas', $order) }}" class="flex items-center gap-1">
                            @csrf @method('PATCH')
                            <input type="number" name="urutan_prioritas" value="{{ $order->urutan_prioritas }}" min="1" class="w-16 text-sm border-gray-300 rounded">
                            <button class="px-2.5 py-1.5 bg-gray-600 text-white text-xs rounded">Ubah Urutan</button>
                        </form>
                        <a href="{{ route('cutting.cetak-spk', $order) }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded">
                            Cetak SPK (QR)
                        </a>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500">Belum ada pesanan siap potong di antrean.</p>
                @endforelse
            </div>
        </div>

        <!-- Buffer: Menunggu Bahan -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4 text-amber-700">2. Ditahan di Buffer (Menunggu Kelengkapan Bahan/Pola)</h3>
            <div class="space-y-2">
                @forelse($menungguBahan as $buf)
                <div class="border p-3 rounded flex justify-between items-center text-sm">
                    <div>
                        <span class="font-mono font-bold">{{ $buf->kode_spk }}</span> — {{ $buf->nama_pelanggan }} ({{ $buf->model_pakaian }}, {{ $buf->jumlah_pcs }} pcs) | Deadline: {{ $buf->tanggal_deadline }}
                    </div>
                    <form method="POST" action="{{ route('pesanan.siap-potong', $buf) }}">
                        @csrf @method('PATCH')
                        <button class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded">Bahan Lengkap - Masukkan Antrean</button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-500">Tidak ada pesanan yang tertahan di buffer.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
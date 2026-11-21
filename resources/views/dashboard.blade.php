<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Produksi — PT Agra Kreasi Widyatama</h2>
            <span class="px-3 py-1 text-xs font-bold rounded-full bg-indigo-100 text-indigo-800 uppercase">Role: {{ auth()->user()->role }}</span>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg font-medium">{{ session('success') }}</div>
        @endif

        <!-- 4 Kartu KPI -->
        @php
            $persenBeban = $kapasitasMingguIni['total_kapasitas'] > 0
                ? min(100, round(($kapasitasMingguIni['beban_terpakai'] / $kapasitasMingguIni['total_kapasitas']) * 100))
                : 0;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-lg shadow border-l-4 {{ $persenBeban > 85 ? 'border-red-500' : 'border-blue-600' }}">
                <p class="text-xs text-gray-500 uppercase font-bold">Beban Cutting (7 Hari)</p>
                <p class="text-2xl font-bold mt-1">{{ $persenBeban }}% <span class="text-sm font-normal text-gray-600">({{ $kapasitasMingguIni['beban_terpakai'] }}/{{ $kapasitasMingguIni['total_kapasitas'] }} Jam)</span></p>
                <p class="text-xs text-gray-500 mt-1">Sisa slot: <strong>{{ $kapasitasMingguIni['sisa_kapasitas'] }} jam</strong></p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow border-l-4 border-amber-500">
                <p class="text-xs text-gray-500 uppercase font-bold">Antrean Meja Cutting</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['antrean_cutting'] }} <span class="text-sm font-normal">SPK</span></p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow border-l-4 border-purple-500">
                <p class="text-xs text-gray-500 uppercase font-bold">Di Vendor Bordir</p>
                <p class="text-2xl font-bold mt-1">{{ $stats['di_vendor'] }} <span class="text-sm font-normal">SPK</span></p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow border-l-4 border-red-600">
                <p class="text-xs text-gray-500 uppercase font-bold">Mendekati Deadline</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['berisiko_telat'] }} <span class="text-sm font-normal text-gray-600">SPK</span></p>
            </div>
        </div>

        <!-- Approval Rush Order (Khusus Manajer) -->
        @if(auth()->user()->role === 'manajer' && $rushOrdersMenunggu->count() > 0)
        <div class="bg-amber-50 border border-amber-300 p-5 rounded-lg shadow">
            <h3 class="font-bold text-amber-900 mb-3">Persetujuan Rush Order (Kapasitas Cutting Penuh)</h3>
            <div class="space-y-2">
                @foreach($rushOrdersMenunggu as $rush)
                <div class="flex flex-wrap justify-between items-center bg-white p-3 rounded border">
                    <div>
                        <span class="font-mono font-bold">{{ $rush->kode_spk }}</span> — {{ $rush->nama_pelanggan }} ({{ $rush->model_pakaian }}, {{ $rush->jumlah_pcs }} pcs / {{ $rush->estimasi_jam_cutting }} jam cutting) | Deadline: <strong>{{ $rush->tanggal_deadline }}</strong>
                    </div>
                    <div class="flex gap-2 mt-2 sm:mt-0">
                        <form method="POST" action="{{ route('pesanan.rush-decision', $rush) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="keputusan" value="disetujui">
                            <button class="px-3 py-1 bg-green-600 text-white text-sm rounded font-semibold">Setujui Prioritas</button>
                        </form>
                        <form method="POST" action="{{ route('pesanan.rush-decision', $rush) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="keputusan" value="ditolak">
                            <button class="px-3 py-1 bg-red-600 text-white text-sm rounded font-semibold">Tolak</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tabel Timeline Produksi Aktif -->
        <div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
            <h3 class="font-bold text-lg mb-4">Monitoring Timeline & Posisi WIP Aktif</h3>
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-700">
                        <th class="p-3">Kode SPK</th>
                        <th class="p-3">Pelanggan & Model</th>
                        <th class="p-3">Kain & Qty</th>
                        <th class="p-3">Beban Cutting</th>
                        <th class="p-3">Posisi Stasiun</th>
                        <th class="p-3">Info Vendor Bordir</th>
                        <th class="p-3">Deadline</th>
                        <th class="p-3">Prediksi Selesai (Sistem)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesananAktif as $item)
                    @php
                        $sisaHari = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($item->tanggal_deadline), false);
                        $badgeDeadline = $sisaHari < 0 ? 'bg-red-100 text-red-800' : ($sisaHari <= 2 ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800');
                    @endphp
                    <tr onclick="window.location='{{ route('pesanan.show', $item) }}'" 
                        class="border-b hover:bg-indigo-50 cursor-pointer transition duration-150">
                        <td class="p-3 font-mono font-bold text-indigo-600 hover:underline">
                            <a href="{{ route('pesanan.show', $item) }}">{{ $item->kode_spk }}</a>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold">{{ $item->nama_pelanggan }}</div>
                            <div class="text-xs text-gray-500">{{ $item->model_pakaian }}</div>
                        </td>
                        <td class="p-3">{{ $item->jenis_kain }} <br><span class="font-bold">{{ $item->jumlah_pcs }} pcs</span></td>
                        <td class="p-3">{{ $item->estimasi_jam_cutting }} jam</td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded bg-blue-100 text-blue-800 uppercase">
                                {{ str_replace('_', ' ', $item->status_produksi) }}
                            </span>
                        </td>
                        <td class="p-3 text-xs">
                            @if($item->status_produksi === 'vendor_bordir' && $item->vendorTerakhir)
                                <strong>{{ $item->vendorTerakhir->nama_vendor }}</strong><br>
                                Est. Kembali: {{ $item->vendorTerakhir->tanggal_estimasi_kembali }}
                            @else
                                {{ $item->pakai_bordir ? 'Ya (Belum/Sudah Bordir)' : '-' }}
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 text-xs font-bold rounded {{ $badgeDeadline }}">
                                {{ $item->tanggal_deadline }} ({{ $sisaHari }} hari)
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-gray-900">{{ $item->prediksi['tgl_prediksi_selesai'] }}</div>
                            <div class="text-[11px] text-gray-500">
                                Est. Potong: {{ $item->prediksi['tgl_selesai_cutting'] }}
                                @if($item->prediksi['tgl_selesai_bordir'])
                                    | Bordir: {{ $item->prediksi['tgl_selesai_bordir'] }}
                                @endif
                            </div>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded {{ $item->prediksi['selisih_hari'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $item->prediksi['status_prediksi'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>    
        </div>

        <!-- Tabel Riwayat SPK Selesai (Completed History) -->
        <div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-gray-800">Riwayat Produksi Selesai (Completed SPK)</h3>
                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                    Total Selesai: {{ $pesananSelesai->count() }} SPK
                </span>
            </div>

            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b text-gray-700">
                        <th class="p-3">Kode SPK</th>
                        <th class="p-3">Pelanggan & Model</th>
                        <th class="p-3">Kain & Qty Akhir</th>
                        <th class="p-3">Total Jam Cutting</th>
                        <th class="p-3">Target Deadline</th>
                        <th class="p-3">Tanggal Selesai Aktual</th>
                        <th class="p-3">Ketepatan Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesananSelesai as $done)
                    @php
                        $tglSelesai = \Carbon\Carbon::parse($done->updated_at)->startOfDay();
                        $tglDeadline = \Carbon\Carbon::parse($done->tanggal_deadline)->startOfDay();
                        $tepatWaktu = $tglSelesai->lte($tglDeadline);
                        $selisih = $tglSelesai->diffInDays($tglDeadline, false);
                    @endphp
                    <tr onclick="window.location='{{ route('pesanan.show', $done) }}'" 
                        class="border-b hover:bg-green-50 cursor-pointer transition duration-150">
                        <td class="p-3 font-mono font-bold text-indigo-600 hover:underline">
                            <a href="{{ route('pesanan.show', $done) }}">{{ $done->kode_spk }}</a>
                        </td>
                        <td class="p-3">
                            <div class="font-semibold">{{ $done->nama_pelanggan }}</div>
                            <div class="text-xs text-gray-500">{{ $done->model_pakaian }}</div>
                        </td>
                        <td class="p-3">{{ $done->jenis_kain }} <br><span class="font-bold">{{ $done->jumlah_pcs }} pcs</span></td>
                        <td class="p-3">{{ $done->estimasi_jam_cutting }} jam</td>
                        <td class="p-3">{{ $tglDeadline->format('d M Y') }}</td>
                        <td class="p-3 font-semibold">{{ \Carbon\Carbon::parse($done->updated_at)->format('d M Y, H:i') }}</td>
                        <td class="p-3">
                            @if($tepatWaktu)
                                <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">
                                    TEPAT WAKTU {{ $selisih > 0 ? "(+$selisih hari)" : '' }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-bold rounded bg-red-100 text-red-800">
                                    TERLAMBAT {{ abs($selisih) }} HARI
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-gray-500 text-sm">
                            Belum ada pesanan yang berstatus selesai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
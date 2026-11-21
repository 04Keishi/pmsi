<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail SPK: {{ $pesanan->kode_spk }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white text-sm rounded">← Kembali</a>
                <a href="{{ route('cutting.cetak-spk', $pesanan) }}" target="_blank" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-bold rounded">Cetak Lembar SPK</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Info Umum & Prediksi -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow space-y-2 text-sm">
                <h3 class="font-bold text-base border-b pb-2 mb-3">Informasi Pesanan</h3>
                <div class="flex justify-between"><span class="text-gray-500">Pelanggan:</span><strong>{{ $pesanan->nama_pelanggan }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Model Pakaian:</span><strong>{{ $pesanan->model_pakaian }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Jenis Kain:</span><strong>{{ $pesanan->jenis_kain }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Kompleksitas Pola:</span><strong>{{ $pesanan->kompleksitas->nama_level }} ({{ $pesanan->kompleksitas->estimasi_menit_per_pcs }} mnt/pcs)</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Vendor Bordir:</span><strong>{{ $pesanan->pakai_bordir ? 'Ya (Eksternal)' : 'Tidak' }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Deadline Klien:</span><strong class="text-red-600">{{ $pesanan->tanggal_deadline }}</strong></div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow space-y-2 text-sm">
                <h3 class="font-bold text-base border-b pb-2 mb-3">Prediksi Timeline Sistem</h3>
                
                <div class="flex justify-between items-center pb-1">
                    <span class="text-gray-500">Status Saat Ini:</span>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-bold rounded uppercase text-xs">
                        {{ str_replace('_', ' ', $pesanan->status_produksi) }}
                    </span>
                </div>

                <div class="space-y-1.5 pt-1 border-t border-dashed">
                    <div class="flex justify-between">
                        <span class="text-gray-600">1. Prediksi Selesai Cutting:</span>
                        <strong class="{{ $prediksi['tgl_selesai_cutting'] === 'Sudah Selesai' ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $prediksi['tgl_selesai_cutting'] }}
                        </strong>
                    </div>

                    @if($prediksi['tgl_selesai_bordir'])
                    <div class="flex justify-between">
                        <span class="text-gray-600">2. Prediksi Kembali Bordir:</span>
                        <strong class="{{ $prediksi['tgl_selesai_bordir'] === 'Sudah Selesai' ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $prediksi['tgl_selesai_bordir'] }}
                        </strong>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ $prediksi['tgl_selesai_bordir'] ? '3.' : '2.' }} Prediksi Selesai Sewing:</span>
                        <strong class="{{ $prediksi['tgl_selesai_sewing'] === 'Sudah Selesai' ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $prediksi['tgl_selesai_sewing'] }}
                        </strong>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ $prediksi['tgl_selesai_bordir'] ? '4.' : '3.' }} Prediksi Selesai QC:</span>
                        <strong class="{{ $prediksi['tgl_selesai_qc'] === 'Sudah Selesai' ? 'text-green-600' : 'text-gray-900' }}">
                            {{ $prediksi['tgl_selesai_qc'] }}
                        </strong>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2 border-t">
                    <span class="text-gray-800 font-bold">{{ $prediksi['tgl_selesai_bordir'] ? '5.' : '4.' }} Packaging / Final Selesai:</span>
                    <strong class="text-lg text-indigo-700">{{ $prediksi['tgl_prediksi_selesai'] }}</strong>
                </div>

                <div class="pt-1 text-right">
                    <span class="px-3 py-1 text-xs font-bold rounded {{ $prediksi['selisih_hari'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $prediksi['status_prediksi'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Ukuran & Perhitungan Waktu Cutting -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-base mb-4">Rincian Ukuran (Size Breakdown) & Kalkulasi Waktu Cutting</h3>
            <table class="w-full text-sm text-left border">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="p-3">Ukuran</th>
                        <th class="p-3">Jumlah (Pcs)</th>
                        <th class="p-3">Standar Kompleksitas</th>
                        <th class="p-3">Pengali Ukuran</th>
                        <th class="p-3 text-right">Estimasi Waktu Potong</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesanan->detailUkuran as $detail)
                    <tr class="border-b">
                        <td class="p-3 font-bold">{{ $detail->ukuran }}</td>
                        <td class="p-3">{{ $detail->jumlah_pcs }} pcs</td>
                        <td class="p-3">{{ $pesanan->kompleksitas->estimasi_menit_per_pcs }} menit/pcs</td>
                        <td class="p-3">{{ $detail->faktor_pengali }}x</td>
                        <td class="p-3 text-right font-mono">{{ number_format($detail->estimasi_jam_ukuran, 2) }} jam</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-sm">
                    <tr class="border-b">
                        <td colspan="4" class="p-3 text-right font-semibold">Tambahan Waktu Setup Ganti Pola ({{ $pesanan->detailUkuran->count() }} Varian Ukuran × 0.25 jam):</td>
                        <td class="p-3 text-right font-mono font-semibold">{{ number_format($pesanan->waktu_setup_pola, 2) }} jam</td>
                    </tr>
                    <tr class="bg-indigo-50 font-bold text-base">
                        <td class="p-3">TOTAL</td>
                        <td class="p-3">{{ $pesanan->jumlah_pcs }} pcs</td>
                        <td colspan="2" class="p-3 text-right">Total Waktu Cutting Keseluruhan:</td>
                        <td class="p-3 text-right font-mono text-indigo-900">{{ number_format($pesanan->estimasi_jam_cutting, 2) }} jam</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Riwayat Log Perpindahan WIP & Status Quality Control -->
        @php
            $logFinishing = $pesanan->logs->whereIn('stasiun_tujuan', ['finishing_packing', 'selesai'])->first();
            $pcsAktif = $logFinishing ? $logFinishing->jumlah_pcs_diproses : $pesanan->jumlah_pcs;
            $sisaKurang = max(0, $pesanan->jumlah_pcs - $pcsAktif);
        @endphp

        <div class="bg-white p-6 rounded-lg shadow space-y-4">
            <div class="flex flex-wrap justify-between items-center border-b pb-3">
                <h3 class="font-bold text-base">Riwayat Perpindahan Divisi & Kontrol Kualitas (QC)</h3>
                <div class="flex gap-2 text-xs font-bold">
                    <span class="px-3 py-1 rounded bg-gray-100 text-gray-800">Target Order: {{ $pesanan->jumlah_pcs }} pcs</span>
                    <span class="px-3 py-1 rounded bg-green-100 text-green-800">Lolos Proses: {{ $pcsAktif }} pcs</span>
                    @if($sisaKurang > 0)
                        <span class="px-3 py-1 rounded bg-red-100 text-red-800">Reject / Kurang: {{ $sisaKurang }} pcs</span>
                    @endif
                </div>
            </div>

            <table class="w-full text-sm text-left border">
                <thead class="bg-gray-100 border-b text-xs uppercase">
                    <tr>
                        <th class="p-2.5">Waktu Scan</th>
                        <th class="p-2.5">Stasiun Tujuan</th>
                        <th class="p-2.5">Lolos / Diproses</th>
                        <th class="p-2.5">Reject (QC)</th>
                        <th class="p-2.5">Keterangan Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesanan->logs as $log)
                    <tr class="border-b">
                        <td class="p-2.5 text-xs text-gray-600">{{ \Carbon\Carbon::parse($log->waktu_scan)->format('d M Y H:i') }}</td>
                        <td class="p-2.5 font-bold uppercase text-indigo-700">{{ str_replace('_', ' ', $log->stasiun_tujuan) }}</td>
                        <td class="p-2.5 font-semibold text-green-700">{{ $log->jumlah_pcs_diproses }} pcs</td>
                        <td class="p-2.5 font-bold {{ $log->jumlah_pcs_reject > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $log->jumlah_pcs_reject }} pcs
                        </td>
                        <td class="p-2.5 text-xs">{{ $log->nama_vendor ? $log->nama_vendor . ' (Est: ' . $log->tanggal_estimasi_kembali . ')' : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-3 text-center text-gray-500 text-xs">Belum ada riwayat scan perpindahan divisi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
</x-app-layout>
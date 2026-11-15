<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Kapasitas Cutting & Kalender Lembur (Manajer)</h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg font-medium">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Pengaturan Kapasitas Standar -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">1. Kapasitas Standar Harian Cutting</h3>
                <form method="POST" action="{{ route('kapasitas.update-standar') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold mb-1">Jumlah Operator Cutting Aktif</label>
                        <input type="number" name="jumlah_operator_cutting" value="{{ $setting->jumlah_operator_cutting ?? 2 }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Jam Kerja Standar per Orang / Hari</label>
                        <input type="number" step="0.5" name="jam_kerja_per_hari" value="{{ $setting->jam_kerja_per_hari ?? 8 }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <button class="px-4 py-2 bg-indigo-600 text-white font-bold rounded">Simpan Standar</button>
                </form>
            </div>

            <!-- Kalender Penyesuaian Harian (Lembur / Operator Absen) -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">2. Pengecualian Harian (Lembur / Operator Izin)</h3>
                <form method="POST" action="{{ route('kapasitas.store-penyesuaian') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold mb-1">Tanggal</label>
                            <input type="date" name="tanggal" required class="w-full border-gray-300 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">Total Jam Kerja Tim Hari Itu</label>
                            <input type="number" step="0.5" name="kapasitas_jam_khusus" placeholder="Misal: 8 atau 24" required class="w-full border-gray-300 rounded text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1">Alasan Penyesuaian</label>
                        <input type="text" name="keterangan" placeholder="Misal: 1 operator sakit / Lembur 4 jam" required class="w-full border-gray-300 rounded text-sm">
                    </div>
                    <button class="px-4 py-2 bg-amber-600 text-white font-bold rounded text-sm">Simpan Penyesuaian Tanggal</button>
                </form>

                <div class="mt-4 border-t pt-3 space-y-2 max-h-40 overflow-y-auto text-xs">
                    @foreach($penyesuaian as $p)
                        <div class="flex justify-between bg-gray-50 p-2 rounded">
                            <span><strong>{{ $p->tanggal }}</strong> — {{ $p->keterangan }}</span>
                            <span class="font-bold text-indigo-700">{{ $p->kapasitas_jam_khusus }} Jam</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Master Kompleksitas Pola -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">3. Standar Waktu Kompleksitas Pola</h3>
            <form method="POST" action="{{ route('kapasitas.store-kompleksitas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                @csrf
                <input type="text" name="nama_level" placeholder="Nama Level (Misal: Level 4 - Jas)" required class="border-gray-300 rounded text-sm">
                <input type="number" name="estimasi_menit_per_pcs" placeholder="Menit per Pcs" required class="border-gray-300 rounded text-sm">
                <input type="text" name="deskripsi" placeholder="Deskripsi kriteria pola" class="border-gray-300 rounded text-sm">
                <button class="bg-gray-800 text-white font-bold rounded text-sm py-2">+ Tambah Level</button>
            </form>
            <table class="w-full text-sm text-left border">
                <tr class="bg-gray-100 border-b">
                    <th class="p-2">Level</th><th class="p-2">Estimasi Waktu</th><th class="p-2">Deskripsi</th>
                </tr>
                @foreach($kompleksitas as $k)
                <tr class="border-b">
                    <td class="p-2 font-bold">{{ $k->nama_level }}</td>
                    <td class="p-2">{{ $k->estimasi_menit_per_pcs }} menit / pcs</td>
                    <td class="p-2 text-gray-600">{{ $k->deskripsi }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</x-app-layout>
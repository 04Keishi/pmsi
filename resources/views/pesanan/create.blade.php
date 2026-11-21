<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Pesanan Baru & Kalkulasi Detail Ukuran</h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pesanan.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            <!-- Kolom Kiri (2 Kolom): Data Pesanan & Rincian Ukuran -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Nama Pelanggan / Instansi</label>
                        <input type="text" name="nama_pelanggan" required class="w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Model Pakaian</label>
                        <input type="text" name="model_pakaian" required placeholder="Misal: Kemeja Seragam" class="w-full border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Jenis & Warna Kain (Batching)</label>
                        <input type="text" name="jenis_kain" required placeholder="Misal: American Drill Navy" class="w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Tingkat Kompleksitas Pola</label>
                        <select id="id_kompleksitas" name="id_kompleksitas" class="w-full border-gray-300 rounded-md">
                            @foreach($kompleksitas as $k)
                                <option value="{{ $k->id }}" data-menit="{{ $k->estimasi_menit_per_pcs }}">
                                    {{ $k->nama_level }} ({{ $k->estimasi_menit_per_pcs }} mnt/pcs)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tabel Input Rincian Ukuran -->
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-sm text-gray-800">Rincian Jumlah per Ukuran (Size Breakdown)</h4>
                        <span class="text-xs text-gray-500">Setiap varian ukuran +0.25 jam setup pola</span>
                    </div>
                    <table class="w-full text-sm text-left bg-white border rounded overflow-hidden">
                        <thead class="bg-gray-100 border-b text-xs uppercase text-gray-600">
                            <tr>
                                <th class="p-2.5">Ukuran</th>
                                <th class="p-2.5">Faktor Waktu</th>
                                <th class="p-2.5 w-32">Jumlah (Pcs)</th>
                                <th class="p-2.5 text-right">Estimasi Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($faktorUkuran as $size => $pengali)
                            <tr class="border-b">
                                <td class="p-2.5 font-bold">{{ $size }}</td>
                                <td class="p-2.5 text-xs text-gray-500">{{ $pengali }}x standar</td>
                                <td class="p-2">
                                    <input type="number" name="qty_ukuran[{{ $size }}]" value="0" min="0"
                                           data-size="{{ $size }}" data-pengali="{{ $pengali }}"
                                           class="input-size w-full border-gray-300 rounded text-sm font-bold text-center">
                                </td>
                                <td class="p-2.5 text-right font-mono text-indigo-700 font-semibold" id="waktu-{{ $size }}">0.00 jam</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-indigo-50 font-bold text-sm">
                            <tr>
                                <td colspan="2" class="p-2.5">Total Keseluruhan</td>
                                <td class="p-2.5 text-center" id="total-pcs-display">0 Pcs</td>
                                <td class="p-2.5 text-right text-indigo-900" id="total-jam-murni-display">0.00 jam</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Target Deadline Pelanggan</label>
                        <input type="date" id="tanggal_deadline" name="tanggal_deadline" required class="w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Status Kesiapan Kain (Buffer)</label>
                        <select name="status_bahan" class="w-full border-gray-300 rounded-md">
                            <option value="siap_potong">Siap Potong (Langsung Antrean Cutting)</option>
                            <option value="belum_siap">Menunggu Bahan (Tahan di Buffer)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="pakai_bordir" value="1" class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm font-semibold">Pesanan ini memerlukan proses Vendor Bordir eksternal</span>
                    </label>
                </div>
            </div>

            <!-- Kolom Kanan: Ringkasan Total Waktu & Cek Kapasitas -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4 h-fit">
                <h3 class="font-bold text-lg border-b pb-2">Ringkasan Waktu Cutting</h3>

                <div class="text-xs space-y-1 bg-gray-50 p-3 rounded border">
                    <div class="flex justify-between">
                        <span>Varian Ukuran Aktif:</span>
                        <strong id="varian-aktif-display">0 ukuran</strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Tambahan Setup Ganti Pola:</span>
                        <strong id="setup-pola-display">0.00 jam</strong>
                    </div>
                </div>

                <input type="hidden" id="waktu_setup_pola" name="waktu_setup_pola" value="0">

                <div>
                    <label class="block text-sm font-bold mb-1">Total Estimasi Jam Cutting</label>
                    <input type="number" step="0.01" id="estimasi_jam_cutting" name="estimasi_jam_cutting" required class="w-full border-gray-300 rounded-md bg-blue-50 font-bold text-xl text-indigo-900">
                    <p class="text-[11px] text-gray-500 mt-1">Sudah termasuk waktu potong tiap ukuran + setup ganti pola (bisa diedit manual).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold mb-1">Catatan Khusus Cutting (Opsional)</label>
                    <input type="text" name="catatan_estimasi" placeholder="Misal: Motif garis harus ketemu" class="w-full border-gray-300 rounded-md text-sm">
                </div>

                <div id="box-kapasitas" class="p-4 rounded-lg bg-gray-100 text-sm hidden">
                    <p id="status-judul" class="font-bold text-base mb-1"></p>
                    <p id="status-detail" class="text-xs"></p>
                </div>

                <div id="box-rush" class="hidden p-3 bg-red-50 border border-red-300 rounded">
                    <label class="inline-flex items-start">
                        <input type="checkbox" name="is_rush_order" value="1" class="mt-1 rounded text-red-600">
                        <span class="ml-2 text-xs text-red-800 font-semibold">Ajukan sebagai Rush Order (Butuh persetujuan Manajer)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md">
                    Simpan Pesanan & Buat SPK
                </button>
            </div>
        </form>
    </div>

    <script>
        const sizeInputs = document.querySelectorAll('.input-size');
        const kompSelect = document.getElementById('id_kompleksitas');
        const jamInput = document.getElementById('estimasi_jam_cutting');
        const setupInput = document.getElementById('waktu_setup_pola');
        const deadlineInput = document.getElementById('tanggal_deadline');

        function hitungSemuaUkuran() {
            const menitDasar = parseFloat(kompSelect.options[kompSelect.selectedIndex].dataset.menit) || 0;
            let totalPcs = 0;
            let totalJamPotong = 0;
            let varianAktif = 0;

            sizeInputs.forEach(input => {
                const qty = parseInt(input.value) || 0;
                const size = input.dataset.size;
                const pengali = parseFloat(input.dataset.pengali) || 1;

                const jamSize = (qty * menitDasar * pengali) / 60;
                document.getElementById(`waktu-${size}`).textContent = jamSize.toFixed(2) + ' jam';

                if (qty > 0) {
                    varianAktif++;
                    totalPcs += qty;
                    totalJamPotong += jamSize;
                }
            });

            // Tambahan 0.25 jam (15 menit) per varian ukuran aktif untuk gelar pola baru
            const waktuSetup = varianAktif > 0 ? (varianAktif * 0.25) : 0;
            const grandTotalJam = (totalJamPotong + waktuSetup).toFixed(2);

            document.getElementById('total-pcs-display').textContent = totalPcs + ' Pcs';
            document.getElementById('total-jam-murni-display').textContent = totalJamPotong.toFixed(2) + ' jam';
            document.getElementById('varian-aktif-display').textContent = varianAktif + ' ukuran';
            document.getElementById('setup-pola-display').textContent = waktuSetup.toFixed(2) + ' jam';

            setupInput.value = waktuSetup.toFixed(2);
            jamInput.value = grandTotalJam;

            cekKapasitasServer();
        }

        async function cekKapasitasServer() {
            const deadline = deadlineInput.value;
            const jam = parseFloat(jamInput.value) || 0;
            if (!deadline || jam <= 0) return;

            const res = await fetch("{{ route('pesanan.cek-kapasitas') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tanggal_deadline: deadline, estimasi_jam: jam })
            });
            const data = await res.json();

            const box = document.getElementById('box-kapasitas');
            const judul = document.getElementById('status-judul');
            const detail = document.getElementById('status-detail');
            const rushBox = document.getElementById('box-rush');

            box.classList.remove('hidden', 'bg-green-100', 'text-green-900', 'bg-red-100', 'text-red-900');
            if (data.layak) {
                box.classList.add('bg-green-100', 'text-green-900');
                judul.textContent = 'Kapasitas Cutting Tersedia';
                detail.textContent = `Sisa kapasitas s/d deadline: ${data.sisa_kapasitas} jam (Kebutuhan: ${jam} jam).`;
                rushBox.classList.add('hidden');
            } else {
                box.classList.add('bg-red-100', 'text-red-900');
                judul.textContent = 'Peringatan: Kapasitas Penuh!';
                detail.textContent = `Sisa kapasitas hanya ${data.sisa_kapasitas} jam, kurang ${Math.abs(data.selisih)} jam.`;
                rushBox.classList.remove('hidden');
            }
        }

        sizeInputs.forEach(inp => inp.addEventListener('input', hitungSemuaUkuran));
        kompSelect.addEventListener('change', hitungSemuaUkuran);
        jamInput.addEventListener('input', cekKapasitasServer);
        deadlineInput.addEventListener('change', cekKapasitasServer);
    </script>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Pesanan Baru & Validasi Kapasitas Cutting</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('pesanan.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf
            <!-- Kolom Kiri: Data Order -->
            <div class="md:col-span-2 bg-white p-6 rounded-lg shadow space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Nama Pelanggan / Instansi</label>
                    <input type="text" name="nama_pelanggan" required class="w-full border-gray-300 rounded-md">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Model Pakaian</label>
                        <input type="text" name="model_pakaian" required placeholder="Misal: Kemeja PDH" class="w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Jenis & Warna Kain (Untuk Batching)</label>
                        <input type="text" name="jenis_kain" required placeholder="Misal: American Drill Navy" class="w-full border-gray-300 rounded-md">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Jumlah Produksi (Pcs)</label>
                        <input type="number" id="jumlah_pcs" name="jumlah_pcs" min="1" required class="w-full border-gray-300 rounded-md">
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
                <div class="grid grid-cols-2 gap-4">
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
                <div class="pt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="pakai_bordir" value="1" class="rounded border-gray-300 text-indigo-600">
                        <span class="ml-2 text-sm font-semibold">Pesanan ini memerlukan proses Vendor Bordir eksternal</span>
                    </label>
                </div>
            </div>

            <!-- Kolom Kanan: Kalkulator Kapasitas Cutting -->
            <div class="bg-white p-6 rounded-lg shadow space-y-4 h-fit">
                <h3 class="font-bold text-lg border-b pb-2">Kalkulasi Beban Cutting</h3>

                <div>
                    <label class="block text-sm font-bold mb-1">Estimasi Jam Cutting (Bisa Diubah)</label>
                    <input type="number" step="0.01" id="estimasi_jam_cutting" name="estimasi_jam_cutting" required class="w-full border-gray-300 rounded-md bg-blue-50 font-bold text-lg">
                    <p class="text-xs text-gray-500 mt-1">Otomatis dari rumus, silakan edit jika desain punya kesulitan khusus.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold mb-1">Catatan Penyesuaian Jam (Opsional)</label>
                    <input type="text" name="catatan_estimasi" placeholder="Misal: Kain motif kotak presisi" class="w-full border-gray-300 rounded-md text-sm">
                </div>

                <!-- Box Hasil Cek Kapasitas AJAX -->
                <div id="box-kapasitas" class="p-4 rounded-lg bg-gray-100 text-sm hidden">
                    <p id="status-judul" class="font-bold text-base mb-1"></p>
                    <p id="status-detail" class="text-xs"></p>
                </div>

                <!-- Opsi Rush Order jika Overload -->
                <div id="box-rush" class="hidden p-3 bg-red-50 border border-red-300 rounded">
                    <label class="inline-flex items-start">
                        <input type="checkbox" name="is_rush_order" value="1" class="mt-1 rounded text-red-600">
                        <span class="ml-2 text-xs text-red-800 font-semibold">Ajukan sebagai Rush Order (Butuh persetujuan Manajer karena kapasitas penuh)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md">
                    Simpan Pesanan & Buat SPK
                </button>
            </div>
        </form>
    </div>

    <script>
        const qtyInput = document.getElementById('jumlah_pcs');
        const kompSelect = document.getElementById('id_kompleksitas');
        const jamInput = document.getElementById('estimasi_jam_cutting');
        const deadlineInput = document.getElementById('tanggal_deadline');

        function hitungOtomatis() {
            const qty = parseFloat(qtyInput.value) || 0;
            const menit = parseFloat(kompSelect.options[kompSelect.selectedIndex].dataset.menit) || 0;
            const jam = ((qty * menit) / 60).toFixed(2);
            jamInput.value = jam;
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
                detail.textContent = `Sisa kapasitas hanya ${data.sisa_kapasitas} jam, kurang ${Math.abs(data.selisih)} jam. Disarankan geser deadline atau ajukan Rush Order.`;
                rushBox.classList.remove('hidden');
            }
        }

        qtyInput.addEventListener('input', hitungOtomatis);
        kompSelect.addEventListener('change', hitungOtomatis);
        jamInput.addEventListener('input', cekKapasitasServer);
        deadlineInput.addEventListener('change', cekKapasitasServer);
    </script>
</x-app-layout>
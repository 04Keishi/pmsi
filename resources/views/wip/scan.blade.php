<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Scan QR SPK & Update Perpindahan Divisi</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-100 text-green-800 rounded-lg font-medium">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Area Scanner Kamera -->
            <div class="bg-white p-5 rounded-lg shadow">
                <h3 class="font-bold mb-3">1. Arahkan Kamera ke QR Lembar SPK</h3>
                <div id="reader" class="w-full border rounded overflow-hidden"></div>
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">Atau Ketik Manual Kode SPK:</label>
                    <div class="flex gap-2">
                        <input type="text" id="input-kode-spk" placeholder="Contoh: SPK-202611-001" class="w-full border-gray-300 rounded text-sm uppercase">
                        <button type="button" onclick="cariDataSpk(document.getElementById('input-kode-spk').value)" class="px-4 py-2 bg-gray-800 text-white text-sm rounded font-bold">Cari</button>
                    </div>
                </div>
            </div>

            <!-- Form Update Status Produksi -->
            <div class="bg-white p-5 rounded-lg shadow">
                <h3 class="font-bold mb-3">2. Konfirmasi Stasiun Tujuan</h3>
                <div id="info-spk" class="hidden p-3 bg-indigo-50 border border-indigo-200 rounded mb-4 text-sm"></div>

                <form method="POST" action="{{ route('wip.update') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" id="form_kode_spk" name="kode_spk" required>

                    <div>
                        <label class="block text-sm font-bold mb-1">Pindahkan ke Stasiun / Proses:</label>
                        <select id="stasiun_tujuan" name="stasiun_tujuan" required class="w-full border-gray-300 rounded">
                            <option value="cutting">Sedang Proses Cutting</option>
                            <option value="vendor_bordir">Kirim ke Vendor Bordir (Eksternal)</option>
                            <option value="sewing">Masuk Sewing (Jahit)</option>
                            <option value="qc">Masuk Quality Control (QC)</option>
                            <option value="finishing_packing">Finishing & Packing</option>
                            <option value="selesai">Selesai & Siap Kirim Klien</option>
                        </select>
                    </div>

                    <!-- Muncul khusus jika pilih Vendor Bordir -->
                    <div id="field-vendor" class="hidden space-y-3 p-3 bg-purple-50 border border-purple-200 rounded">
                        <div>
                            <label class="block text-xs font-bold mb-1">Nama Vendor Bordir</label>
                            <input type="text" name="nama_vendor" placeholder="Misal: Bordir Jaya Abadi" class="w-full border-gray-300 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">Tanggal Estimasi Kembali</label>
                            <input type="date" name="tanggal_estimasi_kembali" class="w-full border-gray-300 rounded text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold mb-1">Jumlah Pcs Diproses</label>
                            <input type="number" id="jumlah_pcs_diproses" name="jumlah_pcs_diproses" required min="1" class="w-full border-gray-300 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1">Jumlah Reject (Saat QC)</label>
                            <input type="number" name="jumlah_pcs_reject" value="0" min="0" class="w-full border-gray-300 rounded text-sm">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded">
                        Simpan Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.getElementById('stasiun_tujuan').addEventListener('change', function() {
            document.getElementById('field-vendor').classList.toggle('hidden', this.value !== 'vendor_bordir');
        });

        async function cariDataSpk(kode) {
            if (!kode) return;
            const res = await fetch(`/scan-wip/cari/${encodeURIComponent(kode.trim())}`);
            const data = await res.json();
            const infoBox = document.getElementById('info-spk');

            if (data.found) {
                infoBox.classList.remove('hidden');
                infoBox.innerHTML = `<strong>${data.pesanan.kode_spk}</strong> — ${data.pesanan.nama_pelanggan}<br>Model: ${data.pesanan.model_pakaian} (${data.pesanan.jumlah_pcs} pcs)<br>Status Saat Ini: <strong class="uppercase">${data.pesanan.status_produksi.replace('_', ' ')}</strong>`;
                document.getElementById('form_kode_spk').value = data.pesanan.kode_spk;
                document.getElementById('input-kode-spk').value = data.pesanan.kode_spk;
                document.getElementById('jumlah_pcs_diproses').value = data.pesanan.jumlah_pcs;
            } else {
                alert(data.message);
            }
        }

        const html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 220 });
        html5QrcodeScanner.render((decodedText) => {
            cariDataSpk(decodedText);
        });
    </script>
</x-app-layout>
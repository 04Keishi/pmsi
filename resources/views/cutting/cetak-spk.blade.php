<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SPK - {{ $pesanan->kode_spk }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Panggil library QRCode.js via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <!-- Tombol Cetak (Hilang saat diprint) -->
    <div class="no-print max-w-xl mx-auto mb-4 flex justify-between">
        <a href="{{ route('cutting.queue') }}" class="px-4 py-2 bg-gray-600 text-white rounded">← Kembali</a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded shadow">
            Cetak Lembar SPK
        </button>
    </div>

    <!-- Lembar SPK Siap Print -->
    <div class="max-w-xl mx-auto bg-white p-8 border-2 border-black">
        <div class="flex justify-between items-start border-b-2 border-black pb-4">
            <div>
                <h1 class="text-xl font-bold uppercase">PT Agra Kreasi Widyatama</h1>
                <p class="text-sm text-gray-600">Surat Perintah Kerja (SPK) Produksi</p>
                <p class="mt-2 text-lg font-mono font-bold">{{ $pesanan->kode_spk }}</p>
            </div>
            
            <!-- Wadah Render QR Code -->
            <div class="text-center">
                <div id="qrcode" class="p-1 border border-gray-300 inline-block"></div>
                <p class="text-[10px] font-mono mt-1">Scan Update WIP</p>
            </div>
        </div>

        <!-- Detail Pesanan -->
        <table class="w-full mt-4 text-sm">
            <tr>
                <td class="py-1 font-semibold w-1/3">Pelanggan</td>
                <td class="py-1">: {{ $pesanan->nama_pelanggan }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Model Pakaian</td>
                <td class="py-1">: {{ $pesanan->model_pakaian }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Jenis Kain</td>
                <td class="py-1">: {{ $pesanan->jenis_kain }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Jumlah Produksi</td>
                <td class="py-1 font-bold">: {{ $pesanan->jumlah_pcs }} Pcs</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Alur Bordir Vendor</td>
                <td class="py-1">: {{ $pesanan->pakai_bordir ? 'YA (Kirim Vendor)' : 'TIDAK (Langsung Sewing)' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold">Target Deadline</td>
                <td class="py-1 font-bold text-red-600">: {{ \Carbon\Carbon::parse($pesanan->tanggal_deadline)->format('d M Y') }}</td>
            </tr>
        </table>

        <!-- Checklist Alur Stasiun -->
        <div class="mt-6 border-t border-black pt-4">
            <p class="text-xs font-bold uppercase mb-2">Checklist Kontrol Stasiun Kerja:</p>
            <div class="grid grid-cols-5 gap-2 text-center text-xs">
                <div class="border border-black p-2">1. Cutting<br><br>[ &nbsp;&nbsp; ]</div>
                <div class="border border-black p-2">2. Bordir<br><br>[ &nbsp;&nbsp; ]</div>
                <div class="border border-black p-2">3. Sewing<br><br>[ &nbsp;&nbsp; ]</div>
                <div class="border border-black p-2">4. QC<br><br>[ &nbsp;&nbsp; ]</div>
                <div class="border border-black p-2">5. Packing<br><br>[ &nbsp;&nbsp; ]</div>
            </div>
        </div>
    </div>

    <!-- Script Generate QR Code Otomatis -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            new QRCode(document.getElementById("qrcode"), {
                text: "{{ $pesanan->kode_spk }}",
                width: 100,
                height: 100,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
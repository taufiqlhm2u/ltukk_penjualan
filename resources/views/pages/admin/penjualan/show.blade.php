<x-sidebar>
    <div>
        <div class="mb-3 flex justify-between items-center">
            <div>
                <flux:heading size="xl" class="font-bold">Detail Penjualan #{{ $penjualan->no_kw }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">Rincian faktur transaksi penjualan</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button icon="printer" variant="primary" color="blue" onclick="printInvoice()">Cetak Invoice</flux:button>
                <flux:button variant="ghost" :href="auth()->user()->peran === 'admin' ? route('admin.penjualan.index') : route('kasir.laporan')">
                    Kembali
                </flux:button>
            </div>
        </div>
        <flux:separator />
        
        <div id="invoice-content" class="bg-white mt-5 p-8 rounded-2xl border border-zinc-200 shadow-xs max-w-4xl">
            <div class="border-b border-zinc-200 pb-4 mb-6 flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">MARKET KUY</h1>
                    <p class="text-xs text-zinc-500 mt-0.5">Sistem Kasir & Penjualan Retail</p>
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-bold text-blue-600">INVOICE PENJUALAN</h2>
                    <p class="text-xs font-semibold text-zinc-800">No. Faktur: {{ $penjualan->no_kw }}</p>
                    <p class="text-xs text-zinc-500">Tanggal: {{ \Carbon\Carbon::parse($penjualan->tgl_penjualan)->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-6 bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-500 mb-2">Informasi Pelanggan</h3>
                    <p class="text-sm font-semibold text-zinc-900">{{ $penjualan->pelanggan->nama ?? 'Pelanggan Umum (Non-Member)' }}</p>
                    @if($penjualan->pelanggan)
                        <p class="text-xs text-zinc-600 mt-1">Telepon: {{ $penjualan->pelanggan->telepon }}</p>
                        <p class="text-xs text-zinc-600">Alamat: {{ $penjualan->pelanggan->alamat }}</p>
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-500 mb-2">Informasi Kasir</h3>
                    <p class="text-sm font-semibold text-zinc-900">{{ $penjualan->kasir->nama ?? '-' }}</p>
                    <p class="text-xs text-zinc-600 mt-1">Username: {{ $penjualan->kasir->username ?? '-' }}</p>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-sm text-zinc-900 mb-3">Rincian Item Produk</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-zinc-200 text-sm">
                        <thead class="bg-zinc-100">
                            <tr>
                                <th class="border border-zinc-200 px-4 py-2.5 text-left font-semibold text-zinc-700">No</th>
                                <th class="border border-zinc-200 px-4 py-2.5 text-left font-semibold text-zinc-700">Nama Produk</th>
                                <th class="border border-zinc-200 px-4 py-2.5 text-right font-semibold text-zinc-700">Harga Satuan</th>
                                <th class="border border-zinc-200 px-4 py-2.5 text-center font-semibold text-zinc-700">Jumlah</th>
                                <th class="border border-zinc-200 px-4 py-2.5 text-right font-semibold text-zinc-700">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($penjualan->details as $index => $detail)
                            <tr>
                                <td class="border border-zinc-200 px-4 py-2 text-zinc-600">{{ $index + 1 }}</td>
                                <td class="border border-zinc-200 px-4 py-2 font-medium text-zinc-900">{{ $detail->produk->nama ?? '-' }}</td>
                                <td class="border border-zinc-200 px-4 py-2 text-right text-zinc-700">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="border border-zinc-200 px-4 py-2 text-center font-semibold text-zinc-800">{{ $detail->jumlah }}</td>
                                <td class="border border-zinc-200 px-4 py-2 text-right font-medium text-zinc-900">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 text-zinc-800">
                            <tr>
                                <td colspan="4" class="border border-zinc-200 px-4 py-2 text-right font-medium">Subtotal:</td>
                                <td class="border border-zinc-200 px-4 py-2 text-right font-medium">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($penjualan->diskon > 0)
                            <tr>
                                <td colspan="4" class="border border-zinc-200 px-4 py-2 text-right text-red-600 font-medium">
                                    Diskon ({{ $penjualan->diskon * 100 }}%):
                                </td>
                                <td class="border border-zinc-200 px-4 py-2 text-right text-red-600 font-medium">
                                    -Rp {{ number_format($penjualan->subtotal * $penjualan->diskon, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr class="bg-blue-50/50">
                                <td colspan="4" class="border border-zinc-200 px-4 py-2.5 text-right font-bold text-zinc-900">TOTAL:</td>
                                <td class="border border-zinc-200 px-4 py-2.5 text-right font-bold text-blue-700 text-base">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="text-center text-zinc-500 text-xs mt-8 pt-4 border-t border-zinc-200">
                <p class="font-medium">Terima kasih atas kunjungan dan pembelian Anda!</p>
                <p class="text-[11px] text-zinc-400 mt-0.5">Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function printInvoice() {
            const printContent = document.getElementById('invoice-content').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Invoice Penjualan #{{ $penjualan->no_kw }}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 30px;
                            color: #333;
                        }
                        h1 { font-size: 20px; font-weight: bold; margin: 0; color: #1e3a8a; }
                        h2 { font-size: 16px; font-weight: bold; margin: 0; color: #2563eb; }
                        h3 { font-size: 12px; font-weight: bold; margin: 0 0 5px 0; text-transform: uppercase; color: #64748b; }
                        p { margin: 2px 0; font-size: 11px; }
                        .border-b { border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 15px; }
                        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
                        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
                        th { background-color: #f1f5f9; font-weight: bold; }
                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                        .text-left { text-align: left; }
                        .font-bold { font-weight: bold; }
                        tfoot { background-color: #f8fafc; }
                        @media print {
                            body { margin: 0; padding: 10px; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 300);
        }
    </script>
    @endpush
</x-sidebar>

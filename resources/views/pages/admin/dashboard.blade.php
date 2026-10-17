<x-sidebar>
    <div class="space-y-6">
        {{-- Header Greeting --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <flux:heading size="xl" class="font-extrabold text-zinc-900">
                    Dashboard Admin
                </flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    Selamat datang kembali, <strong>{{ auth()->user()->nama }}</strong>! Berikut ringkasan aktivitas toko.
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:button variant="outline" icon="printer" :href="route('admin.penjualan.pdf')" target="_blank" size="sm">
                    Cetak Laporan
                </flux:button>
                <flux:button variant="primary" color="blue" icon="plus" :href="route('admin.produk.create')" size="sm">
                    Tambah Produk
                </flux:button>
            </div>
        </div>

        <flux:separator />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Pendapatan --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <flux:icon name="banknotes" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Pendapatan</span>
                    <span class="text-lg font-bold text-emerald-700">
                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Total Transaksi --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <flux:icon name="shopping-bag" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Transaksi</span>
                    <span class="text-lg font-bold text-zinc-900">
                        {{ number_format($totalTransaksi, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Total Produk --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <flux:icon name="cube" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Produk</span>
                    <span class="text-lg font-bold text-zinc-900">
                        {{ number_format($totalProduk, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Total Pelanggan --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <flux:icon name="users" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Total Pelanggan</span>
                    <span class="text-lg font-bold text-zinc-900">
                        {{ number_format($totalPelanggan, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Grid Konten 2 Kolom --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Kolom Kiri: Transaksi Terakhir (8 cols) --}}
            <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" class="font-bold">Transaksi Terbaru</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">5 riwayat penjualan terakhir</flux:text>
                    </div>
                    <flux:button variant="ghost" size="sm" :href="route('admin.penjualan.index')" class="text-blue-600">
                        Lihat Semua
                    </flux:button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 text-xs uppercase">
                            <tr>
                                <th class="py-2.5 px-3">No. Faktur</th>
                                <th class="py-2.5 px-3">Waktu</th>
                                <th class="py-2.5 px-3">Kasir</th>
                                <th class="py-2.5 px-3">Pelanggan</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($transaksiTerakhir as $trx)
                                <tr class="hover:bg-zinc-50 transition">
                                    <td class="py-2.5 px-3 font-semibold text-zinc-900">
                                        <a href="{{ route('admin.penjualan.show', $trx->id) }}" class="text-blue-600 hover:underline">
                                            {{ $trx->no_kw }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 px-3 text-zinc-600 text-xs">
                                        {{ \Carbon\Carbon::parse($trx->tgl_penjualan)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-zinc-700">{{ $trx->kasir->nama ?? '-' }}</td>
                                    <td class="py-2.5 px-3 text-zinc-700">{{ $trx->pelanggan->nama ?? 'Umum' }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-zinc-900">
                                        Rp {{ number_format($trx->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-400">
                                        Belum ada transaksi penjualan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kolom Kanan: Stok Menipis (4 cols) --}}
            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" class="font-bold">Stok Menipis</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Produk perlu di-restock</flux:text>
                    </div>
                    <flux:button variant="ghost" size="sm" :href="route('admin.produk.index')" class="text-blue-600">
                        Semua Produk
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @forelse($stokMenipis as $item)
                        <div class="p-3 rounded-xl border border-zinc-100 bg-zinc-50 flex items-center justify-between gap-2">
                            <div class="min-w-0 grow">
                                <h4 class="font-semibold text-sm text-zinc-900 truncate" title="{{ $item->nama }}">
                                    {{ $item->nama }}
                                </h4>
                                <span class="text-xs text-zinc-500">
                                    {{ $item->kategori->nama ?? 'Umum' }}
                                </span>
                            </div>
                            <div>
                                @if($item->stok == 0)
                                    <flux:badge color="red" size="sm">Habis</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">Sisa {{ $item->stok }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-zinc-400">
                            <flux:icon name="check-circle" class="w-8 h-8 text-green-500 opacity-60 mx-auto mb-1" />
                            <p class="text-xs text-zinc-500 font-medium">Semua stok produk dalam kondisi aman</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-sidebar>
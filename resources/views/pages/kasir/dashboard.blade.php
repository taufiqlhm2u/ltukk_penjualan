<x-sidebar>
    <div class="space-y-6">
        {{-- Banner & Quick POS Launch --}}
        <div class="bg-white p-6 rounded-md border border-zinc-200 shadow-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-zinc-900">Halo, {{ auth()->user()->nama }}</h2>
                <p class="text-zinc-500 text-sm mt-1">Siap melayani transaksi penjualan hari ini? Buka sistem POS untuk memulai.</p>
            </div>
            <flux:button 
                variant="primary" 
                color="blue" 
                icon="shopping-cart" 
                :href="route('kasir.kasir.index')"
            >
                Buka Kasir (POS)
            </flux:button>
        </div>

        {{-- Stat Cards Hari Ini --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Transaksi Hari Ini --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <flux:icon name="shopping-bag" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Transaksi Hari Ini</span>
                    <span class="text-lg font-bold text-zinc-900">
                        {{ number_format($transaksiHariIni, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Pendapatan Hari Ini --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <flux:icon name="banknotes" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Pendapatan Hari Ini</span>
                    <span class="text-lg font-bold text-emerald-700">
                        Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}
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

            {{-- Produk Tersedia --}}
            <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                    <flux:icon name="cube" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-zinc-500 font-medium block">Produk Tersedia</span>
                    <span class="text-lg font-bold text-zinc-900">
                        {{ number_format($totalProduk, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Transaksi Terbaru Hari Ini --}}
        <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg" class="font-bold">Transaksi Hari Ini</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">5 transaksi penjualan yang tercatat hari ini</flux:text>
                </div>
                <flux:button variant="ghost" size="sm" :href="route('kasir.laporan')" class="text-blue-600">
                    Lihat Laporan
                </flux:button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-600 text-xs uppercase">
                        <tr>
                            <th class="py-2.5 px-3">No. Faktur</th>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Pelanggan</th>
                            <th class="py-2.5 px-3">Kasir</th>
                            <th class="py-2.5 px-3 text-right">Total Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($transaksiTerbaru as $trx)
                            <tr class="hover:bg-zinc-50 transition">
                                <td class="py-2.5 px-3 font-semibold text-zinc-900">
                                    <a href="{{ route('kasir.laporan.show', $trx->id) }}" class="text-blue-600 hover:underline">
                                        {{ $trx->no_kw }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-zinc-600 text-xs">
                                    {{ \Carbon\Carbon::parse($trx->tgl_penjualan)->format('H:i:s') }}
                                </td>
                                <td class="py-2.5 px-3 text-zinc-700">
                                    {{ $trx->pelanggan->nama ?? 'Umum' }}
                                </td>
                                <td class="py-2.5 px-3 text-zinc-700">
                                    {{ $trx->kasir->nama ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-zinc-900">
                                    Rp {{ number_format($trx->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-zinc-400">
                                    <flux:icon name="shopping-bag" class="w-8 h-8 opacity-30 mx-auto mb-1.5" />
                                    <p class="text-xs text-zinc-500 font-medium">Belum ada transaksi yang dilakukan hari ini</p>
                                    <div class="mt-2">
                                        <flux:button size="xs" variant="outline" color="blue" :href="route('kasir.kasir.index')">
                                            Mulai Transaksi Pertama
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-sidebar>
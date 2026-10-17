<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Produk;
use App\Models\KategoriProduk;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    // Filter & Search
    public $search = '';
    public $kategori_id = '';
    public $perPage = 12;

    // Cart / Pesanan: [['id' => 1, 'nama' => '', 'merek' => '', 'harga' => 0, 'stok' => 0, 'gambar' => '', 'qty' => 1]]
    public $pesanan = [];

    // Pelanggan Selection
    public $pelanggan_id = '';


    // Pembayaran & Diskon
    public $diskon_tipe = 'persen'; // 'persen' | 'nominal'
    public $diskon = 0;
    public $bayar = 0;

    public function setDiskonTipe(string $tipe)
    {
        $this->diskon_tipe = $tipe;
        $this->diskon = 0;
    }

    public function setDiskonValue($val)
    {
        $this->diskon = (float) $val;
    }

    public function resetDiskon()
    {
        $this->diskon = 0;
    }

    // Struk data
    public $lastPenjualan = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedKategoriId()
    {
        $this->resetPage();
    }

    public function produks()
    {
        return Produk::query()
            ->with('kategori')
            ->when(filled($this->kategori_id), function ($query) {
                $query->where('kategori_produk_id', $this->kategori_id);
            })
            ->when(filled($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('merek', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('harga', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('stok', 'LIKE', '%' . $this->search . '%');
                });
            })
            ->orderBy('nama', 'asc')
            ->paginate($this->perPage);
    }

    public function kategoris()
    {
        return KategoriProduk::orderBy('nama', 'asc')->get();
    }

    public function pelanggans()
    {
        return Pelanggan::orderBy('nama', 'asc')->get();
    }

    public function createPesanan(int $produkId)
    {
        $produk = Produk::find($produkId);
        if (!$produk) {
            $this->js("Flux.toast({ heading: 'Error', text: 'Produk tidak ditemukan.', variant: 'danger' })");
            return;
        }

        if ($produk->stok < 1) {
            $this->js("Flux.toast({ heading: 'Stok Habis', text: 'Produk {$produk->nama} sudah habis.', variant: 'warning' })");
            return;
        }

        foreach ($this->pesanan as $key => $item) {
            if ($item['id'] === $produk->id) {
                if ($item['qty'] >= $produk->stok) {
                    $this->js("Flux.toast({ heading: 'Stok Maksimum', text: 'Jumlah pesanan melebihi stok tersedia ({$produk->stok}).', variant: 'warning' })");
                    return;
                }
                $this->pesanan[$key]['qty']++;
                $this->pesanan[$key]['stok'] = $produk->stok;
                return;
            }
        }

        $this->pesanan[] = [
            'id' => $produk->id,
            'nama' => $produk->nama,
            'merek' => $produk->merek,
            'harga' => (float) $produk->harga,
            'stok' => (int) $produk->stok,
            'gambar' => $produk->gambar,
            'qty' => 1,
        ];
    }

    public function increaseQty(int $index)
    {
        if (!isset($this->pesanan[$index])) {
            return;
        }

        $item = $this->pesanan[$index];
        $produk = Produk::find($item['id']);

        if (!$produk || $item['qty'] >= $produk->stok) {
            $this->js("Flux.toast({ heading: 'Stok Maksimum', text: 'Jumlah pesanan telah mencapai stok maksimal.', variant: 'warning' })");
            return;
        }

        $this->pesanan[$index]['qty']++;
        $this->pesanan[$index]['stok'] = $produk->stok;
    }

    public function decreaseQty(int $index)
    {
        if (!isset($this->pesanan[$index])) {
            return;
        }

        if ($this->pesanan[$index]['qty'] > 1) {
            $this->pesanan[$index]['qty']--;
        } else {
            $this->removeItem($index);
        }
    }

    public function updateQty(int $index, $qty)
    {
        if (!isset($this->pesanan[$index])) {
            return;
        }

        $qty = (int) $qty;
        $item = $this->pesanan[$index];
        $produk = Produk::find($item['id']);
        $maxStok = $produk ? $produk->stok : $item['stok'];

        if ($qty < 1) {
            $this->removeItem($index);
            return;
        }

        if ($qty > $maxStok) {
            $this->pesanan[$index]['qty'] = $maxStok;
            $this->js("Flux.toast({ heading: 'Stok Maksimum', text: 'Jumlah disesuaikan dengan stok yang tersedia ({$maxStok}).', variant: 'warning' })");
            return;
        }

        $this->pesanan[$index]['qty'] = $qty;
    }

    public function removeItem(int $index)
    {
        if (isset($this->pesanan[$index])) {
            unset($this->pesanan[$index]);
            $this->pesanan = array_values($this->pesanan);
        }
    }

    public function clearCart()
    {
        $this->pesanan = [];
        $this->bayar = 0;
        $this->diskon = 0;
        $this->diskon_tipe = 'persen';
        $this->pelanggan_id = '';
    }

    public function getSubtotalProperty(): float
    {
        return (float) collect($this->pesanan)->sum(function ($item) {
            return $item['harga'] * $item['qty'];
        });
    }

    public function getDiskonNominalProperty(): float
    {
        $subtotal = $this->subtotal;
        if ($subtotal <= 0) {
            return 0;
        }

        $val = max(0, (float) $this->diskon);
        if ($this->diskon_tipe === 'persen') {
            $percent = min(100, $val);
            return (float) round($subtotal * ($percent / 100));
        } else {
            return (float) min($subtotal, $val);
        }
    }

    public function getDiskonPersenProperty(): float
    {
        $subtotal = $this->subtotal;
        if ($subtotal <= 0) {
            return 0;
        }
        return (float) round(($this->diskonNominal / $subtotal) * 100, 1);
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->diskonNominal);
    }

    public function getKembalianProperty(): float
    {
        $bayar = (float) $this->bayar;
        return max(0, $bayar - $this->total);
    }

    public function setUangPas()
    {
        $this->bayar = $this->total;
    }

    public function setCashNominal($amount)
    {
        $this->bayar = (float) $amount;
    }

    public function addCashNominal($amount)
    {
        $this->bayar = (float) $this->bayar + (float) $amount;
    }


    public function simpanTransaksi()
    {
        if (empty($this->pesanan)) {
            $this->js("Flux.toast({ heading: 'Pesanan Kosong', text: 'Silakan pilih produk terlebih dahulu.', variant: 'warning' })");
            return;
        }

        $bayar = (float) $this->bayar;
        if ($bayar < $this->total) {
            $this->js("Flux.toast({ heading: 'Pembayaran Kurang', text: 'Nominal pembayaran kurang dari total belanja.', variant: 'danger' })");
            return;
        }

        try {
            DB::beginTransaction();

            // Validasi stok produk di database
            foreach ($this->pesanan as $item) {
                $produk = Produk::lockForUpdate()->find($item['id']);
                if (!$produk || $produk->stok < $item['qty']) {
                    DB::rollBack();
                    $stokTersedia = $produk ? $produk->stok : 0;
                    $this->js("Flux.toast({ heading: 'Stok Tidak Cukup', text: 'Stok {$item['nama']} tidak mencukupi (tersisa {$stokTersedia}).', variant: 'danger' })");
                    return;
                }
            }

            // Generate No Kwitansi
            $today = date('Ymd');
            $countToday = Penjualan::whereDate('created_at', today())->count() + 1;
            $no_kw = 'TRX-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $diskonNominal = $this->diskonNominal;
            $diskonRatio = ($this->subtotal > 0 && $diskonNominal > 0) ? min(0.99, round($diskonNominal / $this->subtotal, 2)) : 0;

            // Simpan Penjualan
            $penjualan = Penjualan::create([
                'no_kw' => $no_kw,
                'kasir_id' => Auth::id() ?? 1,
                'pelanggan_id' => $this->pelanggan_id ?: null,
                'tgl_penjualan' => now(),
                'subtotal' => $this->subtotal,
                'diskon' => $diskonRatio,
                'total' => $this->total,
            ]);

            // Simpan Detail Penjualan & Pengurangan Stok
            $strukItems = [];
            foreach ($this->pesanan as $item) {
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'produk_id' => $item['id'],
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $item['harga'],
                    'diskon' => 0,
                    'total' => $item['harga'] * $item['qty'],
                ]);

                Produk::where('id', $item['id'])->decrement('stok', $item['qty']);

                $strukItems[] = [
                    'nama' => $item['nama'],
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['harga'] * $item['qty'],
                ];
            }

            DB::commit();

            // Data untuk struk
            $pelangganObj = $this->pelanggan_id ? Pelanggan::find($this->pelanggan_id) : null;
            $this->lastPenjualan = [
                'id' => $penjualan->id,
                'no_kw' => $penjualan->no_kw,
                'tanggal' => now()->format('d/m/Y H:i:s'),
                'kasir' => Auth::user()?->nama ?? 'Kasir',
                'pelanggan' => $pelangganObj ? $pelangganObj->nama : 'Umum / Non-Member',
                'telepon' => $pelangganObj?->telepon ?? '-',
                'items' => $strukItems,
                'subtotal' => $this->subtotal,
                'diskon' => $this->diskonPersen,
                'diskon_nominal' => $this->diskonNominal,
                'total' => $this->total,
                'bayar' => $bayar,
                'kembalian' => $this->kembalian,
            ];

            // Reset cart
            $this->clearCart();

            // Tampilkan Struk & Notifikasi
            $this->js("Flux.modal('struk-penjualan').show()");
            $this->js("Flux.toast({ heading: 'Transaksi Berhasil', text: 'Transaksi berhasil disimpan dan struk siap dicetak.', variant: 'success' })");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->js("Flux.toast({ heading: 'Gagal Menyimpan', text: 'Terjadi kesalahan: ' + " . json_encode($e->getMessage()) . ", variant: 'danger' })");
        }
    }
};
?>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
    {{-- KOLOM KIRI: KATALOG PRODUK --}}
    <div class="xl:col-span-7 2xl:col-span-8 bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex flex-col gap-4">
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-between items-stretch sm:items-center">
            <div class="w-full sm:w-80">
                <flux:input 
                    type="search" 
                    placeholder="Cari nama, merek, harga..." 
                    wire:model.live.debounce.300ms="search" 
                    icon="magnifying-glass"
                    clearable
                />
            </div>
            
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="filter-produk">
                    <flux:button icon="funnel" variant="outline">
                        Filter {{ filled($kategori_id) ? '(1)' : '' }}
                    </flux:button>
                </flux:modal.trigger>

                @if(filled($search) || filled($kategori_id))
                    <flux:button variant="ghost" wire:click="$set('search', ''); $set('kategori_id', '');" icon="arrow-path">
                        Reset
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Quick Kategori Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <flux:button 
                size="sm" 
                :variant="$kategori_id === '' ? 'primary' : 'ghost'"
                wire:click="$set('kategori_id', '')"
            >
                Semua
            </flux:button>
            @foreach ($this->kategoris() as $kat)
                <flux:button 
                    size="sm" 
                    :variant="$kategori_id == $kat->id ? 'primary' : 'ghost'"
                    wire:click="$set('kategori_id', '{{ $kat->id }}')"
                >
                    {{ $kat->nama }}
                </flux:button>
            @endforeach
        </div>

        <flux:separator class="my-1!" />

        {{-- Grid Daftar Produk --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 2xl:grid-cols-4 gap-4 overflow-y-auto max-h-145 p-1">
            @forelse ($this->produks() as $row)
                @php
                    $inCart = collect($pesanan)->firstWhere('id', $row->id);
                    $cartQty = $inCart ? $inCart['qty'] : 0;
                    $isOutOfStock = $row->stok < 1;
                    $isMaxInCart = $cartQty >= $row->stok;
                @endphp
                <div class="bg-zinc-50 border border-zinc-200 rounded-xl overflow-hidden flex flex-col justify-between hover:border-blue-400 hover:shadow-sm transition group">
                    {{-- Gambar & Status Badge --}}
                    <div class="relative bg-zinc-100 h-32 w-full overflow-hidden flex items-center justify-center">
                        @if($row->gambar)
                            <img 
                                src="{{ asset('storage/' . $row->gambar) }}" 
                                alt="{{ $row->nama }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            >
                        @else
                            <div class="flex flex-col items-center justify-center text-zinc-400 p-2 text-center">
                                <flux:icon name="photo" class="w-8 h-8 opacity-40 mb-1" />
                                <span class="text-xs">Tidak ada gambar</span>
                            </div>
                        @endif

                        {{-- Badge Kategori --}}
                        <div class="absolute top-2 left-2">
                            <flux:badge size="sm" color="purple" class="shadow-xs text-[11px]!">
                                {{ $row->kategori->nama ?? 'Umum' }}
                            </flux:badge>
                        </div>

                        {{-- Badge Qty di Keranjang --}}
                        @if($cartQty > 0)
                            <div class="absolute top-2 right-2">
                                <flux:badge size="sm" color="blue" class="font-bold shadow-xs">
                                    {{ $cartQty }}x di keranjang
                                </flux:badge>
                            </div>
                        @endif
                    </div>

                    {{-- Informasi Produk --}}
                    <div class="p-3 flex flex-col gap-1.5 grow">
                        <div class="text-xs text-zinc-500 font-medium truncate">
                            {{ $row->merek ?: '-' }}
                        </div>
                        <h4 class="font-semibold text-zinc-900 text-sm line-clamp-2 leading-tight min-h-9" title="{{ $row->nama }}">
                            {{ $row->nama }}
                        </h4>

                        <div class="flex items-center justify-between mt-auto pt-2">
                            <div>
                                <span class="text-xs text-zinc-500 block">Harga</span>
                                <span class="text-sm font-bold text-blue-600">
                                    Rp {{ number_format($row->harga, 0, ',', '.') }}
                                </span>
                            </div>

                            <div>
                                @if($row->stok > 10)
                                    <flux:badge size="sm" color="green">Stok {{ $row->stok }}</flux:badge>
                                @elseif($row->stok > 0)
                                    <flux:badge size="sm" color="yellow">Sisa {{ $row->stok }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="red">Habis</flux:badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Tambah --}}
                    <div class="p-3 pt-0">
                        <flux:button 
                            variant="primary" 
                            color="blue" 
                            size="sm" 
                            class="w-full"
                            icon="plus"
                            wire:click="createPesanan({{ $row->id }})"
                            :disabled="$isOutOfStock || $isMaxInCart"
                        >
                            @if($isOutOfStock)
                                Stok Habis
                            @elseif($isMaxInCart)
                                Maksimal
                            @else
                                Tambah
                            @endif
                        </flux:button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-zinc-400">
                    <flux:icon name="magnifying-glass" class="w-12 h-12 opacity-30 mb-2" />
                    <p class="font-medium text-zinc-600">Produk tidak ditemukan</p>
                    <p class="text-xs text-zinc-400 mt-1">Coba kata kunci lain atau ubah filter kategori.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($this->produks()->hasPages())
            <div class="pt-2">
                {{ $this->produks()->links() }}
            </div>
        @endif
    </div>

    {{-- KOLOM KANAN: KERANJANG & CHECKOUT POS --}}
    <div class="xl:col-span-5 2xl:col-span-4 bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs flex flex-col gap-4 sticky top-4">
        {{-- Header Keranjang --}}
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200">
            <div class="flex items-center gap-2">
                <flux:heading size="lg" class="font-bold flex items-center gap-2">
                    <flux:icon name="shopping-bag" class="w-5 h-5 text-blue-600" />
                    Pesanan
                </flux:heading>
                <flux:badge size="sm" color="blue">{{ count($pesanan) }} Item</flux:badge>
            </div>

            @if(count($pesanan) > 0)
                <flux:button 
                    variant="ghost" 
                    size="sm" 
                    icon="trash" 
                    wire:click="clearCart" 
                    wire:confirm="Yakin ingin mengosongkan keranjang pesanan?"
                    class="text-red-500 hover:text-red-700"
                >
                    Kosongkan
                </flux:button>
            @endif
        </div>

        {{-- Pilih Pelanggan (Nullable / Opsional) --}}
        <div class="bg-zinc-50 p-3 rounded-xl border border-zinc-200 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-zinc-700">Pelanggan (Opsional)</span>
                @if(filled($pelanggan_id))
                    <flux:button variant="ghost" size="xs" wire:click="$set('pelanggan_id', '')" class="text-zinc-500 hover:text-red-500 text-[11px]!">
                        Hapus Pilihan
                    </flux:button>
                @endif
            </div>

            <flux:select wire:model.live="pelanggan_id" placeholder="Pilih Pelanggan">
                <option value="">-- Umum / Tanpa Pelanggan --</option>
                @foreach ($this->pelanggans() as $pel)
                    <option value="{{ $pel->id }}">{{ $pel->nama }} ({{ $pel->telepon }})</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Daftar Item Pesanan --}}
        <div class="flex flex-col gap-2.5 overflow-y-auto max-h-62.5 min-h-35 pr-1 divide-y divide-zinc-100">
            @forelse ($pesanan as $index => $item)
                <div class="pt-2.5 first:pt-0 flex items-center justify-between gap-3">
                    <div class="grow min-w-0">
                        <div class="font-medium text-sm text-zinc-900 truncate" title="{{ $item['nama'] }}">
                            {{ $item['nama'] }}
                        </div>
                        <div class="text-xs text-zinc-500">
                            Rp {{ number_format($item['harga'], 0, ',', '.') }} x {{ $item['qty'] }}
                        </div>
                    </div>

                    {{-- Stepper Qty --}}
                    <div class="flex items-center gap-1.5 bg-zinc-100 p-1 rounded-lg border border-zinc-200">
                        <flux:button 
                            size="xs" 
                            variant="ghost" 
                            icon="minus" 
                            wire:click="decreaseQty({{ $index }})" 
                            class="h-6! w-6! p-0!"
                        />
                        <span class="text-xs font-bold w-6 text-center text-zinc-800">
                            {{ $item['qty'] }}
                        </span>
                        <flux:button 
                            size="xs" 
                            variant="ghost" 
                            icon="plus" 
                            wire:click="increaseQty({{ $index }})"
                            :disabled="$item['qty'] >= $item['stok']"
                            class="h-6! w-6! p-0!"
                        />
                    </div>

                    {{-- Subtotal & Hapus --}}
                    <div class="text-right flex items-center gap-2">
                        <span class="text-sm font-bold text-zinc-900 whitespace-nowrap">
                            Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                        </span>
                        <flux:button 
                            size="xs" 
                            variant="ghost" 
                            icon="x-mark" 
                            wire:click="removeItem({{ $index }})" 
                            class="text-zinc-400 hover:text-red-500 h-6! w-6! p-0!"
                        />
                    </div>
                </div>
            @empty
                <div class="py-8 flex flex-col items-center justify-center text-zinc-400">
                    <flux:icon name="shopping-cart" class="w-10 h-10 opacity-30 mb-2" />
                    <span class="text-sm font-medium text-zinc-600">Keranjang masih kosong</span>
                    <span class="text-xs text-zinc-400 mt-0.5">Pilih produk di sebelah kiri untuk menambahkan.</span>
                </div>
            @endforelse
        </div>

        <flux:separator class="my-1!" />

        {{-- Ringkasan Biaya & Kalkulasi --}}
        <div class="space-y-2.5 text-sm">
            <div class="flex justify-between text-zinc-600">
                <span>Subtotal</span>
                <span class="font-semibold text-zinc-900">
                    Rp {{ number_format($this->subtotal, 0, ',', '.') }}
                </span>
            </div>

            {{-- Diskon Box & Quick Presets --}}
            <div class="bg-zinc-50 border border-zinc-200 p-3 rounded-xl space-y-2.5">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-1.5">
                        <flux:icon name="tag" class="w-4 h-4 text-zinc-500" />
                        <span class="text-xs font-semibold text-zinc-700">Diskon Transaksi</span>
                    </div>

                    {{-- Switcher Persen vs Nominal --}}
                    <div class="flex items-center bg-zinc-200/80 p-0.5 rounded-lg text-xs">
                        <button 
                            type="button"
                            wire:click="setDiskonTipe('persen')" 
                            class="px-2 py-0.5 rounded font-semibold transition {{ $diskon_tipe === 'persen' ? 'bg-white text-blue-600 shadow-xs' : 'text-zinc-600 hover:text-zinc-900' }}"
                        >
                            %
                        </button>
                        <button 
                            type="button"
                            wire:click="setDiskonTipe('nominal')" 
                            class="px-2 py-0.5 rounded font-semibold transition {{ $diskon_tipe === 'nominal' ? 'bg-white text-blue-600 shadow-xs' : 'text-zinc-600 hover:text-zinc-900' }}"
                        >
                            Rp
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        @if($diskon_tipe === 'persen')
                            <flux:input 
                                type="number" 
                                min="0" 
                                max="100" 
                                wire:model.live.debounce.250ms="diskon" 
                                placeholder="0 %" 
                                class="text-sm font-semibold"
                            />
                        @else
                            <flux:input 
                                type="number" 
                                min="0" 
                                wire:model.live.debounce.250ms="diskon" 
                                placeholder="Rp 0" 
                                class="text-sm font-semibold"
                            />
                        @endif
                    </div>

                    @if($this->diskonNominal > 0)
                        <flux:button 
                            size="xs" 
                            variant="ghost" 
                            wire:click="resetDiskon" 
                            class="text-zinc-400 hover:text-red-500" 
                            icon="x-mark"
                            title="Hapus Diskon"
                        />
                    @endif
                </div>

                {{-- Preset Chips --}}
                <div class="flex items-center gap-1 overflow-x-auto pb-0.5">
                    @if($diskon_tipe === 'persen')
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(5)" class="text-[11px]! px-2! py-0.5!">5%</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(10)" class="text-[11px]! px-2! py-0.5!">10%</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(15)" class="text-[11px]! px-2! py-0.5!">15%</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(20)" class="text-[11px]! px-2! py-0.5!">20%</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(50)" class="text-[11px]! px-2! py-0.5!">50%</flux:button>
                    @else
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(5000)" class="text-[11px]! px-1.5! py-0.5!">5rb</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(10000)" class="text-[11px]! px-1.5! py-0.5!">10rb</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(25000)" class="text-[11px]! px-1.5! py-0.5!">25rb</flux:button>
                        <flux:button size="xs" variant="outline" wire:click="setDiskonValue(50000)" class="text-[11px]! px-1.5! py-0.5!">50rb</flux:button>
                    @endif
                </div>

                {{-- Potongan Info --}}
                @if($this->diskonNominal > 0)
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-zinc-200 text-red-600 font-semibold">
                        <span>Potongan Diskon ({{ $this->diskonPersen }}%):</span>
                        <span>-Rp {{ number_format($this->diskonNominal, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Total Akhir --}}
            <div class="bg-blue-50 border border-blue-200 p-3.5 rounded-xl flex justify-between items-center">
                <div>
                    <span class="text-xs text-blue-700 font-bold uppercase tracking-wider block">Total Tagihan</span>
                    <span class="text-xl font-extrabold text-blue-900">
                        Rp {{ number_format($this->total, 0, ',', '.') }}
                    </span>
                </div>
                <flux:badge color="blue">TUNAI</flux:badge>
            </div>

            {{-- Nominal Bayar & Quick Cash --}}
            <div class="space-y-2 pt-1">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-semibold text-zinc-700">Nominal Pembayaran</span>
                    <flux:button 
                        variant="ghost" 
                        size="xs" 
                        wire:click="setUangPas" 
                        :disabled="empty($pesanan)"
                        class="text-blue-600 font-semibold"
                    >
                        Uang Pas
                    </flux:button>
                </div>

                <flux:input 
                    type="number" 
                    min="0" 
                    wire:model.live="bayar" 
                    placeholder="0"
                    icon="banknotes"
                    class="font-bold text-zinc-900"
                />

                {{-- Quick Buttons --}}
                <div class="grid grid-cols-4 gap-1.5 pt-1">
                    <flux:button size="xs" variant="outline" wire:click="setCashNominal(10000)">10.000</flux:button>
                    <flux:button size="xs" variant="outline" wire:click="setCashNominal(20000)">20.000</flux:button>
                    <flux:button size="xs" variant="outline" wire:click="setCashNominal(50000)">50.000</flux:button>
                    <flux:button size="xs" variant="outline" wire:click="setCashNominal(100000)">100.000</flux:button>
                </div>
            </div>

            {{-- Kembalian / Status Pembayaran --}}
            @if((float)$bayar > 0)
                <div class="p-2.5 rounded-lg border {{ (float)$bayar >= $this->total ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }} flex justify-between items-center">
                    <span class="text-xs font-medium">
                        {{ (float)$bayar >= $this->total ? 'Kembalian' : 'Kurang Bayar' }}
                    </span>
                    <span class="font-bold">
                        Rp {{ number_format(abs((float)$bayar - $this->total), 0, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Tombol Simpan & Bayar --}}
        <div class="pt-2">
            <flux:button 
                variant="primary" 
                color="blue" 
                class="w-full font-bold shadow-md py-2.5"
                wire:click="simpanTransaksi" 
                wire:loading.attr="disabled"
                :disabled="empty($pesanan) || (float)$bayar < $this->total"
                icon="check-circle"
            >
                <span wire:loading.remove wire:target="simpanTransaksi">Selesaikan & Cetak Struk</span>
                <span wire:loading wire:target="simpanTransaksi">Memproses Transaksi...</span>
            </flux:button>
        </div>
    </div>

    {{-- MODAL FILTER PRODUK --}}
    <flux:modal name="filter-produk" class="max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Filter Produk</flux:heading>
                <flux:text size="sm">Saring daftar produk berdasarkan kategori.</flux:text>
            </div>
            <flux:separator />
            <div class="space-y-4">
                <div>
                    <flux:select wire:model.live="kategori_id" label="Kategori Produk">
                        <option value="">Semua Kategori</option>
                        @foreach ($this->kategoris() as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="$set('kategori_id', '');">Reset Filter</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Terapkan</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>


    {{-- MODAL STRUK / BUKTI TRANSAKSI --}}
    <flux:modal name="struk-penjualan" class="max-w-md">
        <div class="space-y-4">
            <div class="flex justify-between items-center border-b pb-2">
                <flux:heading size="lg" class="font-bold flex items-center gap-2">
                    <flux:icon name="check-circle" class="w-6 h-6 text-green-600" />
                    Transaksi Berhasil
                </flux:heading>
                <flux:modal.close>
                    <flux:button variant="ghost" size="sm" icon="x-mark" />
                </flux:modal.close>
            </div>

            {{-- Format Struk Thermal --}}
            <div id="struk-pos-content" class="bg-zinc-50 border border-zinc-200 p-4 rounded-xl font-mono text-xs text-zinc-800 space-y-3">
                <div class="text-center space-y-1">
                    <h2 class="text-base font-bold tracking-wider">MARKET KUY</h2>
                    <p class="text-[11px] text-zinc-500">Sistem Aplikasi Kasir & POS</p>
                    <p class="text-[10px] text-zinc-400">Jl. Raya Penjualan No. 123, Indonesia</p>
                </div>

                <div class="border-t border-dashed border-zinc-400 pt-2 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>No. Faktur:</span>
                        <span class="font-bold">{{ $lastPenjualan['no_kw'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu:</span>
                        <span>{{ $lastPenjualan['tanggal'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kasir:</span>
                        <span>{{ $lastPenjualan['kasir'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pelanggan:</span>
                        <span>{{ $lastPenjualan['pelanggan'] ?? 'Umum' }}</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-zinc-400 pt-2">
                    <table class="w-full text-left text-[11px]">
                        <thead>
                            <tr class="border-b border-zinc-300">
                                <th class="pb-1">Item</th>
                                <th class="text-center pb-1">Qty</th>
                                <th class="text-right pb-1">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @if(isset($lastPenjualan['items']))
                                @foreach ($lastPenjualan['items'] as $item)
                                    <tr>
                                        <td class="py-1">
                                            <div class="font-semibold">{{ $item['nama'] }}</div>
                                            <div class="text-[10px] text-zinc-500">@ Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                                        </td>
                                        <td class="text-center py-1 font-semibold">{{ $item['qty'] }}</td>
                                        <td class="text-right py-1 font-semibold">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-dashed border-zinc-400 pt-2 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($lastPenjualan['subtotal'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if(isset($lastPenjualan['diskon']) && $lastPenjualan['diskon'] > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Diskon ({{ $lastPenjualan['diskon'] }}%):</span>
                            <span>-Rp {{ number_format($lastPenjualan['diskon_nominal'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-sm pt-1 border-t border-zinc-300">
                        <span>TOTAL:</span>
                        <span>Rp {{ number_format($lastPenjualan['total'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span>Tunai / Bayar:</span>
                        <span>Rp {{ number_format($lastPenjualan['bayar'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-green-700">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($lastPenjualan['kembalian'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-zinc-400 pt-3 text-center text-[10px] text-zinc-500 space-y-0.5">
                    <p class="font-semibold">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Transaksi Baru</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="printer" onclick="printStrukPos()">
                    Cetak Struk
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

@push('scripts')
<script>
    function printStrukPos() {
        const strukElement = document.getElementById('struk-pos-content');
        if (!strukElement) return;

        const printWindow = window.open('', '_blank', 'width=400,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Struk Pembayaran</title>
                <style>
                    @page {
                        margin: 0;
                        size: 80mm auto;
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        margin: 10px;
                        padding: 0;
                        font-size: 12px;
                        color: #000;
                    }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .font-bold { font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 5px; }
                    th, td { padding: 3px 0; }
                    .border-t { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
                    .border-b { border-bottom: 1px dashed #000; padding-bottom: 5px; }
                    .flex { display: flex; justify-content: space-between; }
                    @media print {
                        body { width: 100%; }
                    }
                </style>
            </head>
            <body>
                ${strukElement.innerHTML}
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

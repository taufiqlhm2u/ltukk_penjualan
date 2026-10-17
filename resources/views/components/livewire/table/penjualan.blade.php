<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Penjualan;
use App\Models\User;
use App\Models\Pelanggan;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $tanggal_dari = '';
    public $tanggal_sampai = '';
    public $kasir_id = '';
    public $pelanggan_id = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTanggalDari()
    {
        $this->resetPage();
    }

    public function updatedTanggalSampai()
    {
        $this->resetPage();
    }

    public function updatedKasirId()
    {
        $this->resetPage();
    }

    public function updatedPelangganId()
    {
        $this->resetPage();
    }

    public function setHariIni()
    {
        $this->tanggal_dari = date('Y-m-d');
        $this->tanggal_sampai = date('Y-m-d');
        $this->resetPage();
    }

    public function setBulanIni()
    {
        $this->tanggal_dari = date('Y-m-01');
        $this->tanggal_sampai = date('Y-m-t');
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->search = '';
        $this->tanggal_dari = '';
        $this->tanggal_sampai = '';
        $this->kasir_id = '';
        $this->pelanggan_id = '';
        $this->resetPage();
    }

    private function getFilteredQuery()
    {
        $user = auth()->user();

        return Penjualan::query()
            ->with(['kasir', 'pelanggan', 'details.produk'])
            ->when($user && $user->peran === 'kasir', function ($query) use ($user) {
                $query->where('kasir_id', $user->id);
            })
            ->when($user && $user->peran === 'admin' && filled($this->kasir_id), function ($query) {
                $query->where('kasir_id', $this->kasir_id);
            })
            ->when(filled($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('no_kw', 'LIKE', '%' . $this->search . '%')
                        ->orWhereHas('pelanggan', function ($sub) {
                            $sub->where('nama', 'LIKE', '%' . $this->search . '%');
                        })
                        ->orWhereHas('kasir', function ($sub) {
                            $sub->where('nama', 'LIKE', '%' . $this->search . '%');
                        });
                });
            })
            ->when(filled($this->tanggal_dari), function ($query) {
                $query->whereDate('tgl_penjualan', '>=', $this->tanggal_dari);
            })
            ->when(filled($this->tanggal_sampai), function ($query) {
                $query->whereDate('tgl_penjualan', '<=', $this->tanggal_sampai);
            })
            ->when(filled($this->pelanggan_id), function ($query) {
                $query->where('pelanggan_id', $this->pelanggan_id);
            });
    }

    public function penjualans()
    {
        return $this->getFilteredQuery()
            ->orderBy('tgl_penjualan', 'desc')
            ->paginate(10);
    }

    public function kasirs()
    {
        return User::where('peran', 'kasir')->orderBy('nama', 'asc')->get();
    }

    public function pelanggans()
    {
        return Pelanggan::orderBy('nama', 'asc')->get();
    }

    public function getSummaryProperty(): array
    {
        $all = $this->getFilteredQuery()->get();
        return [
            'totalTransaksi' => $all->count(),
            'totalOmset' => (float) $all->sum('total'),
            'totalDiskon' => (float) $all->sum(fn($p) => $p->subtotal * ($p->diskon ?? 0)),
            'totalItem' => (int) $all->sum(fn($p) => $p->details->sum('jumlah')),
        ];
    }
};
?>

<div class="space-y-5 mt-5">
    {{-- Widget Ringkasan Statistik --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Transaksi --}}
        <div class="bg-white p-4.5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <flux:icon name="shopping-bag" class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-zinc-500 font-medium block">Total Transaksi</span>
                <span class="text-lg font-bold text-zinc-900">
                    {{ number_format($this->summary['totalTransaksi'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Total Pendapatan / Omset --}}
        <div class="bg-white p-4.5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <flux:icon name="banknotes" class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-zinc-500 font-medium block">Total Pendapatan</span>
                <span class="text-lg font-bold text-emerald-700">
                    Rp {{ number_format($this->summary['totalOmset'], 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Total Item Terjual --}}
        <div class="bg-white p-4.5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <flux:icon name="cube" class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-zinc-500 font-medium block">Item Terjual</span>
                <span class="text-lg font-bold text-purple-700">
                    {{ number_format($this->summary['totalItem'], 0, ',', '.') }} pcs
                </span>
            </div>
        </div>

        {{-- Total Diskon --}}
        <div class="bg-white p-4.5 rounded-2xl border border-zinc-200 shadow-xs flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <flux:icon name="tag" class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-zinc-500 font-medium block">Total Diskon Diberikan</span>
                <span class="text-lg font-bold text-amber-700">
                    Rp {{ number_format($this->summary['totalDiskon'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-xs">
        <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="w-full sm:w-72">
                    <flux:input 
                        type="search" 
                        placeholder="Cari faktur, pelanggan, kasir..." 
                        wire:model.live.debounce.300ms="search" 
                        icon="magnifying-glass"
                        clearable 
                    />
                </div>

                {{-- Preset Buttons --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                    <flux:button size="sm" variant="ghost" wire:click="setHariIni">Hari Ini</flux:button>
                    <flux:button size="sm" variant="ghost" wire:click="setBulanIni">Bulan Ini</flux:button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $exportPdfUrl = auth()->user()->peran === 'admin' 
                        ? route('admin.penjualan.pdf', [
                            'tanggal_dari' => $tanggal_dari,
                            'tanggal_sampai' => $tanggal_sampai,
                            'kasir_id' => $kasir_id,
                        ])
                        : route('kasir.laporan.pdf', [
                            'tanggal_dari' => $tanggal_dari,
                            'tanggal_sampai' => $tanggal_sampai,
                        ]);
                @endphp

                <flux:button 
                    icon="printer" 
                    variant="primary" 
                    color="blue" 
                    href="{{ $exportPdfUrl }}" 
                    target="_blank"
                >
                    Export PDF
                </flux:button>

                <flux:modal.trigger name="filter-penjualan">
                    <flux:button icon="funnel" variant="outline">
                        Filter {{ (filled($tanggal_dari) || filled($tanggal_sampai) || filled($kasir_id) || filled($pelanggan_id)) ? '(Aktif)' : '' }}
                    </flux:button>
                </flux:modal.trigger>

                @if(filled($search) || filled($tanggal_dari) || filled($tanggal_sampai) || filled($kasir_id) || filled($pelanggan_id))
                    <flux:button variant="ghost" wire:click="resetFilter" icon="arrow-path">
                        Reset
                    </flux:button>
                @endif
            </div>
        </div>

        <flux:separator class="my-4!" />

        <flux:table :paginate="$this->penjualans()">
            <flux:table.columns>
                <flux:table.column>No</flux:table.column>
                <flux:table.column>No. Faktur</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
                <flux:table.column>Kasir</flux:table.column>
                <flux:table.column>Pelanggan</flux:table.column>
                <flux:table.column class="text-right!">Subtotal</flux:table.column>
                <flux:table.column class="text-right!">Diskon</flux:table.column>
                <flux:table.column class="text-right!">Total</flux:table.column>
                <flux:table.column class="text-center!">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->penjualans() as $no => $row)
                    @php
                        $diskonNominal = $row->subtotal * ($row->diskon ?? 0);
                    @endphp
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->penjualans()->firstItem() + $no }}</flux:table.cell>
                        <flux:table.cell class="font-bold text-zinc-900">{{ $row->no_kw }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row->tgl_penjualan)->format('d/m/Y H:i') }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $row->kasir->nama ?? '-' }}</flux:table.cell>
                        <flux:table.cell>
                            @if($row->pelanggan)
                                <span class="font-medium text-zinc-800">{{ $row->pelanggan->nama }}</span>
                            @else
                                <span class="text-zinc-400">Umum</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right! text-zinc-700">
                            Rp {{ number_format($row->subtotal, 0, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell class="text-right!">
                            @if($diskonNominal > 0)
                                <span class="text-red-500 font-medium">-Rp {{ number_format($diskonNominal, 0, ',', '.') }}</span>
                            @else
                                <span class="text-zinc-400">-</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right! font-bold text-zinc-900">
                            Rp {{ number_format($row->total, 0, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell class="flex items-center justify-center gap-2">
                            @php
                                $detailUrl = auth()->user()->peran === 'admin' 
                                    ? route('admin.penjualan.show', $row->id) 
                                    : route('kasir.laporan.show', $row->id);
                            @endphp
                            <flux:button 
                                icon="eye" 
                                variant="filled"
                                href="{{ $detailUrl }}" 
                                size="sm"
                                class="bg-blue-50! text-blue-600! hover:bg-blue-100!"
                                title="Lihat Detail Invoice"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell class="text-center! py-12! text-zinc-400" colspan="9">
                            <i>Data penjualan tidak ditemukan pada filter yang dipilih</i>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Modal Filter Penjualan --}}
    <flux:modal name="filter-penjualan" class="max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Filter Laporan Penjualan</flux:heading>
                <flux:text size="sm">Tentukan rentang tanggal dan parameter filter.</flux:text>
            </div>
            <flux:separator />

            <div class="space-y-4">
                <div>
                    <flux:input wire:model.live="tanggal_dari" label="Tanggal Dari" type="date" />
                </div>
                <div>
                    <flux:input wire:model.live="tanggal_sampai" label="Tanggal Sampai" type="date" />
                </div>

                @if(auth()->user()->peran === 'admin')
                    <div>
                        <flux:select wire:model.live="kasir_id" label="Kasir">
                            <option value="">Semua Kasir</option>
                            @foreach ($this->kasirs() as $k)
                                <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <div>
                    <flux:select wire:model.live="pelanggan_id" label="Pelanggan">
                        <option value="">Semua Pelanggan</option>
                        @foreach ($this->pelanggans() as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="resetFilter">Reset</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Terapkan</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

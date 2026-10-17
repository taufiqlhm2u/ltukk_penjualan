<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pelanggan;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $jenis_kelamin = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedJenisKelamin()
    {
        $this->resetPage();
    }

    public function pelanggans()
    {
        return Pelanggan::query()
            ->when(filled($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('nama', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('telepon', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('alamat', 'LIKE', '%' . $this->search . '%');
                });
            })
            ->when(filled($this->jenis_kelamin), function ($query) {
                $query->where('jenis_kelamin', $this->jenis_kelamin);
            })
            ->orderBy('nama', 'asc')
            ->paginate(10);
    }
};
?>

<div class="bg-white mt-5 p-5 rounded-2xl border border-zinc-200 shadow-xs">
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <div class="w-full sm:w-80">
            <flux:input 
                type="search" 
                placeholder="Cari nama, telepon, alamat..." 
                wire:model.live.debounce.300ms="search" 
                icon="magnifying-glass"
                clearable 
            />
        </div>

        <div class="flex items-center gap-2">
            <flux:modal.trigger name="filter-pelanggan">
                <flux:button icon="funnel" variant="outline">
                    Filter {{ filled($jenis_kelamin) ? '(1)' : '' }}
                </flux:button>
            </flux:modal.trigger>

            @if(filled($search) || filled($jenis_kelamin))
                <flux:button variant="ghost" wire:click="$set('search', ''); $set('jenis_kelamin', '');" icon="arrow-path">
                    Reset
                </flux:button>
            @endif
        </div>
    </div>

    <flux:separator class="my-4!" />

    <flux:table :paginate="$this->pelanggans()">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Nama Pelanggan</flux:table.column>
            <flux:table.column>Telepon</flux:table.column>
            <flux:table.column>Jenis Kelamin</flux:table.column>
            <flux:table.column>Alamat</flux:table.column>
            <flux:table.column class="text-center!">Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->pelanggans() as $no => $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>{{ $this->pelanggans()->firstItem() + $no }}</flux:table.cell>
                    <flux:table.cell class="font-medium text-zinc-900">{{ $row->nama }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-700">{{ $row->telepon }}</flux:table.cell>
                    <flux:table.cell>
                        @if($row->jenis_kelamin === 'laki-laki')
                            <flux:badge color="blue" size="sm">Laki-laki</flux:badge>
                        @else
                            <flux:badge color="pink" size="sm">Perempuan</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-600 max-w-xs truncate" title="{{ $row->alamat }}">
                        {{ $row->alamat }}
                    </flux:table.cell>
                    <flux:table.cell class="flex items-center justify-center gap-2">
                        <flux:button 
                            icon="pencil-square" 
                            variant="filled"
                            href="{{ route('kasir.pelanggan.edit', $row->id) }}" 
                            size="sm"
                            class="bg-blue-100! text-blue-600!" 
                        />

                        <flux:modal.trigger :name="'hapus-pelanggan-' . $row->id">
                            <flux:button 
                                icon="trash" 
                                variant="filled" 
                                size="sm"
                                class="bg-red-500! text-white!" 
                            />
                        </flux:modal.trigger>
                    </flux:table.cell>
                </flux:table.row>

                {{-- Modal Konfirmasi Hapus --}}
                <flux:modal :name="'hapus-pelanggan-' . $row->id" class="max-w-md">
                    <form method="POST" action="{{ route('kasir.pelanggan.destroy', $row->id) }}" class="space-y-5">
                        @csrf
                        @method('DELETE')
                        <div>
                            <flux:heading size="lg">Konfirmasi Hapus Pelanggan</flux:heading>
                            <flux:text size="sm" class="mt-2 text-zinc-600">
                                Apakah kamu yakin ingin menghapus pelanggan <strong>{{ $row->nama }}</strong>? Data riwayat transaksi yang terkait mungkin ikut terpengaruh.
                            </flux:text>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">Batal</flux:button>
                            </flux:modal.close>
                            <flux:button class="bg-red-600! text-white!" type="submit">Ya, Hapus</flux:button>
                        </div>
                    </form>
                </flux:modal>
            @empty
                <flux:table.row>
                    <flux:table.cell class="text-center! py-8! text-zinc-400" colspan="6">
                        <i>Data pelanggan tidak ditemukan</i>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Filter Pelanggan --}}
    <flux:modal name="filter-pelanggan" class="max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Filter Pelanggan</flux:heading>
                <flux:text size="sm">Saring daftar pelanggan berdasarkan kriteria.</flux:text>
            </div>
            <flux:separator />
            <div class="space-y-4">
                <div>
                    <flux:select wire:model.live="jenis_kelamin" label="Jenis Kelamin">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="laki-laki">Laki-laki</option>
                        <option value="perempuan">Perempuan</option>
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:button variant="ghost" wire:click="$set('jenis_kelamin', '');">Reset</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

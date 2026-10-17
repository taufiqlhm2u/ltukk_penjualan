<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Produk;

new class extends Component {
    use WithPagination;

    public $search;
    public $kategori_id;

    public function produks()
    {
        return Produk::query()
            ->with('kategori')
            ->when(filled($this->kategori_id), function ($query) {
                $query->where('kategori_produk_id', $this->kategori_id);
            })
            ->when(filled($this->search), function ($query) {
                $query
                    ->where('nama', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('merek', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('harga', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('stok', 'LIKE', '%' . $this->search . '%');
            })
            ->paginate();
    }

    public function kategoris()
    {
        return \App\Models\KategoriProduk::all();
    }
};
?>

<div class="bg-white mt-5 p-4 rounded">
    <div class="flex justify-between">
        <flux:input type="search" placeholder="Cari produk" wire:model.live.debounce.300="search" class="w-50!" />
        <flux:modal.trigger name="filter-produk">
            <flux:button icon="funnel" variant="ghost">Filter</flux:button>
        </flux:modal.trigger>
    </div>
    <flux:separator class="my-3!" />
    <flux:table :paginate="$this->produks()">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Gambar</flux:table.column>
            <flux:table.column>Nama Produk</flux:table.column>
            <flux:table.column>Merek</flux:table.column>
            <flux:table.column>Kategori</flux:table.column>
            <flux:table.column>Harga</flux:table.column>
            <flux:table.column>Stok</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->produks() as $no => $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>{{ $this->produks()->firstItem() + $no }}</flux:table.cell>
                    <flux:table.cell>
                        @if($row->gambar)
                            <img src="{{ asset('storage/' . $row->gambar) }}" alt="{{ $row->nama }}" class="w-16 h-16 object-cover rounded">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                <span class="text-xs text-gray-500 text-center">Tidak ada <br> gambar</span>
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->nama }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->merek }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="purple">{{ $row->kategori->nama ?? '-' }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-800">Rp {{ number_format($row->harga, 0, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>
                        @if($row->stok > 10)
                            <flux:badge color="green">{{ $row->stok }}</flux:badge>
                        @elseif($row->stok > 0)
                            <flux:badge color="yellow">{{ $row->stok }}</flux:badge>
                        @else
                            <flux:badge color="red">{{ $row->stok }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="flex items-center gap-2 justify-center">
                        <flux:button icon="pencil-square" variant="filled"
                            href="{{ route('admin.produk.edit', $row->id) }}" size="sm"
                            class="bg-blue-100! text-blue-600!" />

                        <flux:modal.trigger :name="__('hapus-data-').$row->id">
                            <flux:button icon="trash" variant="filled" size="sm"
                                class="bg-red-500! text-white!" />
                        </flux:modal.trigger>

                    </flux:table.cell>
                    
                </flux:table.row>

                {{-- modal hapus --}}
                <flux:modal :name="__('hapus-data-').$row->id" class="max-w-96">
                    <form method="post" action="{{route('admin.produk.destroy', $row->id)}}" class="space-y-6">
                        @csrf
                        @method('DELETE')
                        <div class="mb-5!">
                            <flux:heading size="lg">Konfirmasi hapus</flux:heading>
                            <flux:text>
                                Data yang dihapus tidak dapat dikembalikan kembali. Apakah kamu yakin ingin
                                menghapus data?
                            </flux:text>
                        </div>
                        <div class="flex justify-end">
                            <div class="flex gap-2">
                                <flux:modal.close>
                                    <flux:button variant="ghost">Batal</flux:button>
                                </flux:modal.close>
                                <flux:button class="bg-red-500! text-white!" type="submit">Ya, hapus</flux:button>
                            </div>
                        </div>
                    </form>
                </flux:modal>
            @empty
                <flux:table.row>
                    <flux:table.cell class="text-center! pt-8!" colspan="8">
                        <i>Data tidak ditemukan</i>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Filter --}}
    <flux:modal name="filter-produk" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Filter Produk</flux:heading>
            </div>
            <flux:separator />
            <div class="space-y-4">
                <div>
                    <flux:select wire:model.live="kategori_id" label="Kategori">
                        <option value="">Semua Kategori</option>
                        @foreach($this->kategoris() as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('kategori_id', '');">Reset</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

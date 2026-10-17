<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\KategoriProduk;

new class extends Component {
    use WithPagination;

    public $search;

    public function kategoris()
    {
        return KategoriProduk::query()
            ->when(filled($this->search), function ($query) {
                $query
                    ->where('nama_kategori', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('deskripsi', 'LIKE', '%' . $this->search . '%');
            })
            ->paginate();
    }
};
?>

<div class="bg-white mt-5 p-4 rounded">
    <div class="flex justify-between">
        <flux:input type="search" placeholder="Cari kategori" wire:model.live.debounce.300="search" class="w-50!" />
    </div>
    <flux:separator class="my-3!" />
    <flux:table :paginate="$this->kategoris()">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Nama Kategori</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->kategoris() as $no => $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>{{ $this->kategoris()->firstItem() + $no }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->nama }}</flux:table.cell>
                    <flux:table.cell class="flex items-center gap-2">
                        <flux:button icon="pencil-square" variant="filled"
                            href="{{ route('admin.kategori.edit', $row->id) }}" size="sm"
                            class="bg-blue-100! text-blue-600!" />

                        <flux:modal.trigger :name="__('hapus-data-').$row->id">
                            <flux:button icon="trash" variant="filled" size="sm"
                                class="bg-red-500! text-white!" />
                        </flux:modal.trigger>

                    </flux:table.cell>
                    
                </flux:table.row>

                {{-- modal hapus --}}
                <flux:modal :name="__('hapus-data-').$row->id" class="max-w-96">
                    <form method="post" action="{{route('admin.kategori.destroy', $row->id)}}" class="space-y-6">
                        @csrf
                        @method('DELETE')
                        <div>
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
                    <flux:table.cell class="text-center! pt-8!" colspan="3">
                        <i>Data tidak ditemukan</i>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

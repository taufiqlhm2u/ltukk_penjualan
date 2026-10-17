<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $gender;
    public $role;
    public $status;
    public $search;

    public function users()
    {
        return User::query()
            ->whereNot('id', Auth::user()->id)
            ->when(filled($this->gender), function ($query) {
                $query->where('jenis_kelamin', $this->gender);
            })
            ->when(filled($this->role), function ($query) {
                $query->where('peran', $this->role);
            })
            ->when(filled($this->status), function ($query) {
                $query->where('status_aktif', $this->status);
            })
            ->when(filled($this->search), function ($query) {
                $query
                    ->where('nama', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('username', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('telepon', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('alamat', 'LIKE', '%' . $this->search . '%');
            })
            ->paginate();
        // return User::paginate(10);
    }
};
?>

<div class="bg-white mt-5 p-4 rounded">
    <div class="flex justify-between">
        <flux:input type="search" placeholder="Cari user" wire:model.live.debounce.300="search" class="w-50!" />
        <flux:modal.trigger name="filter-user">
            <flux:button icon="funnel" variant="ghost">Filter</flux:button>
        </flux:modal.trigger>
    </div>
    <flux:separator class="my-3!" />
    <flux:table :paginate="$this->users()">
        <flux:table.columns>
            <flux:table.column>No</flux:table.column>
            <flux:table.column>Username</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Telepon</flux:table.column>
            <flux:table.column>Jenis Kelamin</flux:table.column>
            <flux:table.column>Alamat</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->users() as $no => $row)
                <flux:table.row :key="$row->id">
                    <flux:table.cell>{{ $this->users()->firstItem() + $no }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->username }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->nama }}</flux:table.cell>
                    <flux:table.cell>{{ $row->telepon }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-800">{{ $row->jenis_kelamin }}</flux:table.cell>
                    <flux:table.cell>{{ $row->alamat }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($row->peran == 'admin')
                            <flux:badge color="blue">{{ $row->peran }}</flux:badge>
                        @else
                            <flux:badge color="purple">{{ $row->peran }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($row->status_aktif)
                            <flux:badge color="green">Aktif</flux:badge>
                        @else
                            <flux:badge color="red">Tidak Aktif</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="flex items-center gap-2">
                        <flux:button icon="pencil-square" variant="filled"
                            href="{{ route('admin.user.edit', $row->id) }}" size="sm"
                            class="bg-blue-100! text-blue-600!" />

                        <flux:modal.trigger :name="__('hapus-data-').$row->id">
                            <flux:button icon="trash" variant="filled" size="sm"
                                class="bg-red-500! text-white!" />
                        </flux:modal.trigger>

                    </flux:table.cell>
                    
                </flux:table.row>

                {{-- modal hapsu --}}
                        <flux:modal :name="__('hapus-data-').$row->id" class="max-w-96">
                            <form method="post" action="{{route('admin.user.destroy', $row->id)}}" class="space-y-6">
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
                    <flux:table.cell class="text-center! pt-8!" colspan="9">
                        <i>Data tidak ditemukan</i>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Modal Filter --}}
    <flux:modal name="filter-user" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Filter User</flux:heading>
            </div>
            <flux:separator />
            <div class="space-y-4">
                <div>
                    <flux:select wire:model.live="gender" label="Jenis Kelamin">
                        <option value="">Semua</option>
                        <option value="laki-laki">Laki-laki</option>
                        <option value="perempuan">Perempuan</option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="role" label="Role">
                        <option value="">Semua</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model.live="status" label="Status">
                        <option value="">Semua</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </flux:select>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('gender', ''); $set('role', ''); $set('status', '');">Reset</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>

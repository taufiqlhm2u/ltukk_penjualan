<x-sidebar>
    <div>
        <div class="mb-3 flex justify-between items-center">
            <div>
                <flux:heading size="xl" class="font-bold">Data Pelanggan</flux:heading>
                <flux:text size="sm" class="text-zinc-500">Kelola dan lihat seluruh data pelanggan toko</flux:text>
            </div>
            <flux:button color="blue" variant="primary" icon="plus" :href="route('kasir.pelanggan.create')">
                Tambah Pelanggan
            </flux:button>
        </div>
        <flux:separator />

        @livewire('livewire.table.pelanggan')
    </div>
</x-sidebar>

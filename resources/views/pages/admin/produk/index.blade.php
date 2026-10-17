<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Data Produk</flux:heading>
            <flux:button color="blue" variant="primary" :href="route('admin.produk.create')">Tambah</flux:button>
        </div>
        <flux:separator />
            @livewire('livewire.table.produk')
    </div>
</x-sidebar>

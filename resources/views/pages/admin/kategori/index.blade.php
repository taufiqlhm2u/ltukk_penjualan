<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Data Kategori Produk</flux:heading>
            <flux:button color="blue" variant="primary" :href="route('admin.kategori.create')">Tambah</flux:button>
        </div>
        <flux:separator />
            @livewire('livewire.table.kategori')
    </div>
</x-sidebar>

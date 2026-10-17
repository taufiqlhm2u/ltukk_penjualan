<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Data User</flux:heading>
            <flux:button color="blue" variant="primary" :href="route('admin.user.create')">Tambah</flux:button>
        </div>
        <flux:separator />
            @livewire('livewire.table.user')
    </div>
</x-sidebar>
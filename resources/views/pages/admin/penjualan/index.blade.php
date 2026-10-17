<x-sidebar>
    <div>
        <div class="mb-3 flex justify-between items-center">
            <div>
                <flux:heading size="xl" class="font-bold">Laporan & Riwayat Penjualan</flux:heading>
                <flux:text size="sm" class="text-zinc-500">Monitoring rekap transaksi, pendapatan, dan ekspor laporan penjualan toko</flux:text>
            </div>
        </div>
        <flux:separator />

        @livewire('livewire.table.penjualan')
    </div>
</x-sidebar>

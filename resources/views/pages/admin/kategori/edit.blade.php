<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Edit Kategori Produk</flux:heading>
            <flux:button variant="ghost" :href="route('admin.kategori.index')">Kembali</flux:button>
        </div>
        <flux:separator />
        <div class="bg-white mt-5 p-8 rounded-md">
            <form method="POST" action="{{ route('admin.kategori.update', $kategori->id) }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 md:gap-8 md:grid-cols-2">
                    <div>
                        <flux:input name="nama_kategori" label="Nama Kategori" placeholder="Makanan Ringan" type="text" :value="old('nama_kategori', $kategori->nama_kategori)" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <flux:button variant="ghost" :href="route('admin.kategori.index')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-sidebar>

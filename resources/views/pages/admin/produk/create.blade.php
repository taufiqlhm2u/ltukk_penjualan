<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Tambah Produk</flux:heading>
            <flux:button variant="ghost" :href="route('admin.produk.index')">Kembali</flux:button>
        </div>
        <flux:separator />
        <div class="bg-white mt-5 p-8 rounded-md">
            <form method="POST" action="{{ route('admin.produk.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:gap-8 md:grid-cols-2">
                    <div>
                        <flux:input name="nama_produk" label="Nama Produk" placeholder="Chitato Rasa Sapi" type="text" :value="old('nama_produk', '')" />
                    </div>
                    <div>
                        <flux:input name="merek" label="Merek" placeholder="Chitato" type="text" :value="old('merek', '')" />
                    </div>
                    <div>
                        <flux:select name="kategori_produk_id" label="Kategori">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_produk_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:input name="harga" label="Harga" placeholder="15000" type="number" :value="old('harga', '')" />
                    </div>
                    <div>
                        <flux:input name="stok" label="Stok" placeholder="100" type="number" :value="old('stok', '')" />
                    </div>
                    <div class="col-span-2">
                        <flux:input name="gambar" label="Gambar Produk" type="file" accept="image/*" />
                        <flux:text class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB</flux:text>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <flux:button variant="ghost" :href="route('admin.produk.index')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-sidebar>

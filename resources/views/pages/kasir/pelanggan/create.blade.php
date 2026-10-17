<x-sidebar>
    <div>
        <div class="mb-3 flex justify-between items-center">
            <div>
                <flux:heading size="xl" class="font-bold">Tambah Pelanggan</flux:heading>
                <flux:text size="sm" class="text-zinc-500">Daftarkan data pelanggan baru</flux:text>
            </div>
            <flux:button variant="ghost" :href="route('kasir.pelanggan.index')" icon="arrow-left">
                Kembali
            </flux:button>
        </div>
        <flux:separator />

        <div class="bg-white mt-5 p-6 sm:p-8 rounded-2xl border border-zinc-200 shadow-xs max-w-3xl">
            <form method="POST" action="{{ route('kasir.pelanggan.store') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <flux:input 
                            name="nama" 
                            label="Nama Pelanggan" 
                            placeholder="Contoh: Budi Santoso" 
                            type="text" 
                            :value="old('nama', '')" 
                            required 
                        />
                        @error('nama') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input 
                            name="telepon" 
                            label="Nomor Telepon" 
                            placeholder="Contoh: 081234567890" 
                            type="text" 
                            :value="old('telepon', '')" 
                            required 
                        />
                        @error('telepon') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <flux:select name="jenis_kelamin" label="Jenis Kelamin" required>
                            <option value="laki-laki" {{ old('jenis_kelamin') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan" {{ old('jenis_kelamin') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </flux:select>
                        @error('jenis_kelamin') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <flux:input 
                            name="alamat" 
                            label="Alamat Lengkap" 
                            placeholder="Contoh: Jl. Merdeka No. 45, Kota Bandung" 
                            type="text" 
                            :value="old('alamat', '')" 
                            required 
                        />
                        @error('alamat') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-zinc-200">
                    <flux:button variant="ghost" :href="route('kasir.pelanggan.index')">Batal</flux:button>
                    <flux:button type="submit" variant="primary" color="blue">Simpan Pelanggan</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-sidebar>

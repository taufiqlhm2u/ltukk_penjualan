<x-sidebar>
    <div class="">
        <div class="mb-3 flex justify-between items-center">
            <flux:heading size="xl">Tambah User</flux:heading>
            <flux:button variant="ghost" :href="route('admin.user.index')">Kembali</flux:button>
        </div>
        <flux:separator />
        <div class="bg-white mt-5 p-8 rounded-md">
            <form method="POST" action="{{ route('admin.user.store') }}">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:gap-8 md:grid-cols-2">
                    <div>
                        <flux:input name="username" label="Buat username" placeholder="john23" type="text" :value="old('username', '')" />
                    </div>
                    <div>
                        <flux:input name="nama" label="Nama Lengkap" placeholder="John Snow" type="text" :value="old('nama', '')" />
                    </div>
                    <div>
                        <flux:input name="password" label="Buat Password" type="password" placeholder="******" :value="old('password', '')" viewable />
                    </div>
                    <div>
                        <flux:input name="password_confirmation" label="Konfirmasi Password" type="password" placeholder="******" :value="old('password_confirmation', '')" viewable />
                    </div>
                    <div>
                        <flux:input name="telepon" label="No. Telp" placeholder="0872****" type="text" :value="old('telepon', '')" />
                    </div>
                    <div>
                        <flux:select name="gender" label="Jenis Kelamin">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:select name="peran" label="Role">
                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('peran') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="kasir" {{ old('peran') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        </flux:select>
                    </div>
                     <div class="col-span-2">
                        <flux:textarea name="alamat" label="Alamat" placeholder="Jl. ..."> 
                            {{old('alamat', '')}}
                        </flux:textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <flux:button variant="ghost" :href="route('admin.user.index')">Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-sidebar>
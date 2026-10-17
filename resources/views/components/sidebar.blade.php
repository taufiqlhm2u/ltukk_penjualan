<x-layout.app>
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    </flux:header>

    <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 border-r border-zinc-200">
        <flux:sidebar.header>
            <flux:sidebar.brand name="Market Kuy" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @auth
                @if (auth()->user()->peran === 'admin')
                    <flux:sidebar.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')">
                        Dashboard</flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.user.index')" :current="request()->routeIs('admin.user*')">User
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.kategori.index')"
                        :current="request()->routeIs('admin.kategori*')">
                        Kategori</flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.produk.index')" :current="request()->routeIs('admin.produk*')">
                        Produk
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('admin.penjualan.index')"
                        :current="request()->routeIs('admin.penjualan*')">
                        Penjualan</flux:sidebar.item>
                @else
                    <flux:sidebar.item :href="route('kasir.dashboard')" :current="request()->routeIs('kasir.dashboard')">
                        Dashboard</flux:sidebar.item>
                    <flux:sidebar.item :href="route('kasir.kasir.index')" :current="request()->routeIs('kasir.kasir*')">
                        Kasir
                    </flux:sidebar.item>
                    <flux:sidebar.item :href="route('kasir.pelanggan.index')"
                        :current="request()->routeIs('kasir.pelanggan*')">
                        Pelanggan</flux:sidebar.item>
                    <flux:sidebar.item :href="route('kasir.laporan')" :current="request()->routeIs('kasir.laporan')">
                        Laporan Penjualan</flux:sidebar.item>
                @endif
            @endauth
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:dropdown>
            <flux:profile :name="auth()->user()->nama" />
            <flux:menu>
                <flux:modal.trigger name="logout">
                    <flux:menu.item class="text-red-500 hover:bg-red-100">
                        Logout
                    </flux:menu.item>
                </flux:modal.trigger>
            </flux:menu>
        </flux:dropdown>

        <flux:modal name="logout">
            <form action="{{ route('logout') }}" method="post" class="space-y-6">
                @csrf
                <div>
                    <flux:heading size="lg">Logout</flux:heading>
                    <flux:text>Apakah kamu yakin ingin logout?</flux:text>
                </div>
                <div class="flex justify-end">
                    <div class="flex gap-3">
                        <flux:modal.close>
                            <flux:button variant="ghost">Batal</flux:button>
                        </flux:modal.close>
                        <flux:button variant="danger" type="submit">Logout</flux:button>
                    </div>
                </div>
            </form>
        </flux:modal>
    </flux:sidebar>

    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layout.app>

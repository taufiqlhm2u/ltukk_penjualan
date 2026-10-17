<x-layout.app title="Well">
    <div class="min-h-screen w-full flex justify-center items-center">
        <div class="bg-white shadow-2xl p-4 w-100 rounded-md">
            <h1 class="font-semibold text-3xl">Login</h1>
            <flux:separator class="bg-black my-5" />
            <form action="{{route('login.verif')}}" method="post">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                        <flux:field>
                            <flux:input type="text" :label="__('Username')" :placeholder="__('Masukan username')" name="username" />
                        </flux:field>

                        <flux:field>
                            <flux:input :label="__('Password')" :placeholder="__('Masukan Password')" name="password" viewable />
                        </flux:field>

                        <flux:button variant="primary" type="submit">Login</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layout.app>
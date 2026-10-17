<x-layout.app title="Login">
    <div class="min-h-screen w-full flex justify-center items-center">
        <div class="bg-white shadow-2xl p-4 w-100 rounded-md">
            <h1 class="font-semibold text-3xl">Login</h1>
            <flux:separator class="my-5" />
            <form action="{{route('login.verif')}}" method="post">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                        <flux:field>
                            <flux:input type="text" :label="__('Username')" :placeholder="__('Masukan username')" name="username" :value="old('username', '')" />
                        </flux:field>

                        <flux:field>
                            <flux:input type="password" :label="__('Password')" :placeholder="__('Masukan Password')" name="password" viewable :value="old('password', '')" />
                        </flux:field>

                        <flux:button variant="primary" class="bg-blue-500 hover:bg-blue-600 transition-all text-white" type="submit">Login</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layout.app>
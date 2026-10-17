<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('password'),
                'alamat' => $faker->address(),
                'telepon' => $faker->phoneNumber(),
                'jenis_kelamin' => 'laki-laki',
                'peran' => 'admin'
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'nama' => 'Mas Kasir',
                'password' => Hash::make('password'),
                'alamat' => $faker->address(),
                'telepon' => $faker->phoneNumber(),
                'jenis_kelamin' => 'laki-laki',
                'peran' => 'kasir'
            ]
        );

        $this->call([
            ProdukSeeder::class,
        ]);
    }
}

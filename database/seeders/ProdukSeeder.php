<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriData = [
            'Makanan & Minuman' => [
                ['nama' => 'Aqua 600ml', 'merek' => 'Danone', 'harga' => 3500, 'stok' => 50],
                ['nama' => 'Teh Botol Sosro 350ml', 'merek' => 'Sosro', 'harga' => 4500, 'stok' => 40],
                ['nama' => 'Indomie Goreng Spesial', 'merek' => 'Indofood', 'harga' => 3500, 'stok' => 100],
                ['nama' => 'Indomie Ayam Bawang', 'merek' => 'Indofood', 'harga' => 3500, 'stok' => 80],
                ['nama' => 'Kopi Kapal Api Spesial Mix', 'merek' => 'Kapal Api', 'harga' => 2000, 'stok' => 120],
                ['nama' => 'Ultra Milk Cokelat 250ml', 'merek' => 'Ultra Milk', 'harga' => 6500, 'stok' => 35],
                ['nama' => 'Pocari Sweat 500ml', 'merek' => 'Otsuka', 'harga' => 7500, 'stok' => 30],
                ['nama' => 'Le Minerale 600ml', 'merek' => 'Mayora', 'harga' => 3500, 'stok' => 45],
            ],
            'Snack & Makanan Ringan' => [
                ['nama' => 'Chitato Sapi Panggang 68g', 'merek' => 'Indofood', 'harga' => 11500, 'stok' => 25],
                ['nama' => 'Oreo Vanilla 133g', 'merek' => 'Mondelez', 'harga' => 9000, 'stok' => 30],
                ['nama' => 'SilverQueen Cashew 58g', 'merek' => 'Petra', 'harga' => 16500, 'stok' => 20],
                ['nama' => 'Pocky Chocolate 45g', 'merek' => 'Glico', 'harga' => 8500, 'stok' => 25],
                ['nama' => 'Roma Biskuit Kelapa 300g', 'merek' => 'Mayora', 'harga' => 10500, 'stok' => 18],
                ['nama' => 'Beng-Beng Regular 25g', 'merek' => 'Mayora', 'harga' => 2500, 'stok' => 60],
            ],
            'Sembako & Bumbu Dapur' => [
                ['nama' => 'Beras Ramos Premium 5kg', 'merek' => 'Ramos', 'harga' => 72000, 'stok' => 15],
                ['nama' => 'Minyak Goreng Bimoli 2L', 'merek' => 'Bimoli', 'harga' => 36000, 'stok' => 20],
                ['nama' => 'Gula Pasir Gulaku 1kg', 'merek' => 'Gulaku', 'harga' => 18000, 'stok' => 30],
                ['nama' => 'Tepung Terigu Segitiga Biru 1kg', 'merek' => 'Bogasari', 'harga' => 13000, 'stok' => 25],
                ['nama' => 'Kecap Manis Bango 520ml', 'merek' => 'Unilever', 'harga' => 24500, 'stok' => 20],
                ['nama' => 'Garam Dapur Cap Kapal 250g', 'merek' => 'Cap Kapal', 'harga' => 4000, 'stok' => 40],
            ],
            'Perawatan Tubuh & Kebersihan' => [
                ['nama' => 'Sabun Mandi Lifebuoy Total 10 85g', 'merek' => 'Unilever', 'harga' => 4500, 'stok' => 50],
                ['nama' => 'Shampoo Sunsilk Black Shine 160ml', 'merek' => 'Unilever', 'harga' => 22000, 'stok' => 18],
                ['nama' => 'Pasta Gigi Pepsodent 190g', 'merek' => 'Unilever', 'harga' => 14500, 'stok' => 30],
                ['nama' => 'Deterjen Rinso Molto 770g', 'merek' => 'Unilever', 'harga' => 21000, 'stok' => 22],
                ['nama' => 'Sabun Cuci Piring Sunlight Lime 750ml', 'merek' => 'Unilever', 'harga' => 16000, 'stok' => 28],
            ],
            'Kebutuhan Rumah Tangga & ATK' => [
                ['nama' => 'Tissue Paseo 250 Sheets', 'merek' => 'Paseo', 'harga' => 14500, 'stok' => 30],
                ['nama' => 'Buku Tulis Sinar Dunia 38 Lembar', 'merek' => 'SIDU', 'harga' => 4000, 'stok' => 50],
                ['nama' => 'Pulpen Pilot G2 0.5 Black', 'merek' => 'Pilot', 'harga' => 15000, 'stok' => 35],
                ['nama' => 'Baterai ABC Alkaline AA 2pcs', 'merek' => 'ABC', 'harga' => 13500, 'stok' => 20],
            ],
        ];

        foreach ($kategoriData as $kategoriNama => $produks) {
            $kategori = KategoriProduk::firstOrCreate(['nama' => $kategoriNama]);

            foreach ($produks as $item) {
                Produk::updateOrCreate(
                    ['nama' => $item['nama']],
                    [
                        'kategori_produk_id' => $kategori->id,
                        'merek' => $item['merek'],
                        'harga' => $item['harga'],
                        'gambar' => '-',
                        'stok' => $item['stok'],
                    ]
                );
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPendapatan = Penjualan::sum('total');
        $totalTransaksi = Penjualan::count();
        $totalProduk = Produk::count();
        $totalPelanggan = Pelanggan::count();

        // 5 Transaksi Terakhir
        $transaksiTerakhir = Penjualan::with(['kasir', 'pelanggan'])
            ->orderBy('tgl_penjualan', 'desc')
            ->limit(5)
            ->get();

        // Produk Stok Menipis (stok <= 5)
        $stokMenipis = Produk::with('kategori')
            ->where('stok', '<=', 5)
            ->orderBy('stok', 'asc')
            ->limit(5)
            ->get();

        return view('pages.admin.dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'totalProduk',
            'totalPelanggan',
            'transaksiTerakhir',
            'stokMenipis'
        ));
    }
}

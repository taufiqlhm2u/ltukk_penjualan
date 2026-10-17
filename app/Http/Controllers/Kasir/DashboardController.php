<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $kasirId = Auth::id();

        // Transaksi & Pendapatan Kasir Ini Hari Ini
        $transaksiHariIni = Penjualan::where('kasir_id', $kasirId)
            ->whereDate('tgl_penjualan', $today)
            ->count();

        $pendapatanHariIni = Penjualan::where('kasir_id', $kasirId)
            ->whereDate('tgl_penjualan', $today)
            ->sum('total');

        $totalPelanggan = Pelanggan::count();
        $totalProduk = Produk::where('stok', '>', 0)->count();

        // 5 Transaksi Terbaru Hari Ini oleh Kasir Ini
        $transaksiTerbaru = Penjualan::with(['kasir', 'pelanggan'])
            ->where('kasir_id', $kasirId)
            ->whereDate('tgl_penjualan', $today)
            ->orderBy('tgl_penjualan', 'desc')
            ->limit(5)
            ->get();

        return view('pages.kasir.dashboard', compact(
            'transaksiHariIni',
            'pendapatanHariIni',
            'totalPelanggan',
            'totalProduk',
            'transaksiTerbaru'
        ));
    }
}

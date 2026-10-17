<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Display sales report page for cashier.
     */
    public function index()
    {
        return view('pages.kasir.laporan.index');
    }

    /**
     * Display the specified sales transaction detail for cashier (only their own transactions).
     */
    public function show(string $id)
    {
        $penjualan = Penjualan::with(['kasir', 'pelanggan', 'details.produk'])
            ->where('kasir_id', Auth::id())
            ->findOrFail($id);

        return view('pages.admin.penjualan.show', compact('penjualan'));
    }

    /**
     * Export sales report to PDF for cashier (only their own transactions).
     */
    public function cetakPdf(Request $request)
    {
        $start = $request->tanggal_dari;
        $end = $request->tanggal_sampai;

        $query = Penjualan::with(['kasir', 'pelanggan', 'details.produk'])
            ->where('kasir_id', Auth::id())
            ->when(filled($start), function ($q) use ($start) {
                $q->whereDate('tgl_penjualan', '>=', $start);
            })
            ->when(filled($end), function ($q) use ($end) {
                $q->whereDate('tgl_penjualan', '<=', $end);
            })
            ->orderBy('tgl_penjualan', 'desc');

        $penjualans = $query->get();

        $totalTransaksi = $penjualans->count();
        $totalSubtotal = $penjualans->sum('subtotal');
        $totalDiskon = $penjualans->sum(fn($p) => $p->subtotal * ($p->diskon ?? 0));
        $totalOmset = $penjualans->sum('total');
        $totalItem = $penjualans->sum(fn($p) => $p->details->sum('jumlah'));

        $userCetak = Auth::user();
        $kasir = $userCetak;

        $pdf = Pdf::loadView('pages.laporan.pdf', compact(
            'penjualans',
            'start',
            'end',
            'totalTransaksi',
            'totalSubtotal',
            'totalDiskon',
            'totalOmset',
            'totalItem',
            'kasir',
            'userCetak'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Penjualan-Kasir-' . date('YmdHis') . '.pdf');
    }
}

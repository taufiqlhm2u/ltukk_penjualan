<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.admin.penjualan.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $penjualan = Penjualan::with(['kasir', 'pelanggan', 'details.produk'])->findOrFail($id);
        return view('pages.admin.penjualan.show', compact('penjualan'));
    }

    /**
     * Export sales report to PDF.
     */
    public function cetakPdf(Request $request)
    {
        $start = $request->tanggal_dari;
        $end = $request->tanggal_sampai;
        $kasir_id = $request->kasir_id;

        $query = Penjualan::with(['kasir', 'pelanggan', 'details.produk'])
            ->when(filled($start), function ($q) use ($start) {
                $q->whereDate('tgl_penjualan', '>=', $start);
            })
            ->when(filled($end), function ($q) use ($end) {
                $q->whereDate('tgl_penjualan', '<=', $end);
            })
            ->when(filled($kasir_id), function ($q) use ($kasir_id) {
                $q->where('kasir_id', $kasir_id);
            })
            ->orderBy('tgl_penjualan', 'desc');

        $penjualans = $query->get();

        $totalTransaksi = $penjualans->count();
        $totalSubtotal = $penjualans->sum('subtotal');
        $totalDiskon = $penjualans->sum(fn($p) => $p->subtotal * ($p->diskon ?? 0));
        $totalOmset = $penjualans->sum('total');
        $totalItem = $penjualans->sum(fn($p) => $p->details->sum('jumlah'));

        $kasir = $kasir_id ? User::find($kasir_id) : null;
        $userCetak = Auth::user();

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

        return $pdf->stream('Laporan-Penjualan-Admin-' . date('YmdHis') . '.pdf');
    }
}

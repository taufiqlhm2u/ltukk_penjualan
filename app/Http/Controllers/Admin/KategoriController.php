<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use Exception;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.admin.kategori.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|min:3|unique:kategori_produks,nama',
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        try {
            KategoriProduk::create([
                'nama' => $request->nama_kategori,
            ]);

            return redirect()->route('admin.kategori.index')->with('success', 'Data kategori berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat menambahkan data kategori.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        return view('pages.admin.kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|min:3|unique:kategori_produks,nama,' . $id,
        ]);

        try {
            $kategori = KategoriProduk::findOrFail($id);
            
            $kategori->update([
                'nama' => $request->nama_kategori,
            ]);

            return redirect()->route('admin.kategori.index')->with('success', 'Data kategori berhasil diupdate.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat mengupdate data kategori.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $kategori = KategoriProduk::findOrFail($id);

            $kategori->delete();
            return redirect()->route('admin.kategori.index')->with('success', 'Berhasil menghapus data kategori.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat menghapus data kategori.');
        }
    }
}

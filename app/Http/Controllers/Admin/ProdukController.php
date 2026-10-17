<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.admin.produk.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = KategoriProduk::orderBy('nama', 'ASC')->get();
        return view('pages.admin.produk.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|min:3',
            'kategori_produk_id' => 'required|exists:kategori_produks,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'merek' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = [
                'nama' => $request->nama_produk,
                'merek' => $request->merek,
                'kategori_produk_id' => $request->kategori_produk_id,
                'harga' => $request->harga,
                'stok' => $request->stok,
            ];

            // Handle image upload
            if ($request->hasFile('gambar')) {
                // Cek dan buat folder jika belum ada
                if (!Storage::disk('public')->exists('produk')) {
                    Storage::disk('public')->makeDirectory('produk');
                }

                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('produk', $imageName, 'public');
                $data['gambar'] = 'produk/' . $imageName;
            } else {
                $data['gambar'] = '';
            }

            Produk::create($data);

            return redirect()->route('admin.produk.index')->with('success', 'Data produk berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat menambahkan data produk: ' . $e->getMessage())->withInput();
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
        $produk = Produk::findOrFail($id);
        $kategoris = KategoriProduk::orderBy('nama', 'ASC')->get();
        return view('pages.admin.produk.edit', compact('produk', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|min:3',
            'kategori_produk_id' => 'required|exists:kategori_produks,id',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'merek' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $produk = Produk::findOrFail($id);

            $data = [
                'nama' => $request->nama_produk,
                'merek' => $request->merek,
                'kategori_produk_id' => $request->kategori_produk_id,
                'harga' => $request->harga,
                'stok' => $request->stok,
            ];

            // Handle image upload
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama jika ada
                if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                    Storage::disk('public')->delete($produk->gambar);
                }

                // Cek dan buat folder jika belum ada
                if (!Storage::disk('public')->exists('produk')) {
                    Storage::disk('public')->makeDirectory('produk');
                }

                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('produk', $imageName, 'public');
                $data['gambar'] = 'produk/' . $imageName;
            }

            $produk->update($data);

            return redirect()->route('admin.produk.index')->with('success', 'Data produk berhasil diupdate.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat mengupdate data produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produk = Produk::findOrFail($id);

            // Hapus gambar jika ada
            if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                Storage::disk('public')->delete($produk->gambar);
            }

            $produk->delete();
            return redirect()->route('admin.produk.index')->with('success', 'Berhasil menghapus data produk.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat menghapus data produk.');
        }
    }
}

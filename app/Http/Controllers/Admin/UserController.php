<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:5|unique:users,username',
            'nama' => 'required|string|min:5',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
            'telepon' => 'required',
            'alamat' => 'required|string|max:1000',
            'gender' => 'required|in:laki-laki,perempuan',
            'peran' => 'required|in:admin,kasir',
        ]);

        try {
            User::create([
                'username' => $request->username,
                'nama' => $request->nama,
                'password' => $request->password,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->gender,
                'peran' => $request->peran,
            ]);

            return redirect()->route('admin.user.index')->with('success', 'Data user berhasil ditambahkan.');
        } catch (Exception $e) {
            // return back()->with('error', 'Ada kesalahan saat menambahkan data user.')->withInput();
            return $e->getMessage();
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
        $user = User::findOrFail($id);
        return view('pages.admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'username' => 'required|string|min:5|unique:users,username,' . $id,
            'nama' => 'required|string|min:5',
            'telepon' => 'required',
            'alamat' => 'required|string|max:1000',
            'gender' => 'required|in:laki-laki,perempuan',
            'peran' => 'required|in:admin,kasir',
            'status_aktif' => 'required|in:0,1',
        ];

        // Jika password diisi, tambahkan validasi
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8';
            $rules['password_confirmation'] = 'required|string|min:8|same:password';
        }

        $request->validate($rules);

        try {
            $data = [
                'username' => $request->username,
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'jenis_kelamin' => $request->gender,
                'peran' => $request->peran,
                'status_aktif' => $request->status_aktif,
            ];

            // Update password jika diisi
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $user->update($data);

            return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diupdate.');
        } catch (Exception $e) {
            return back()->with('error', 'Ada kesalahan saat mengupdate data user.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();
            return redirect()->route('admin.user.index')->with('success', 'Berhasil menghapus data user.');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

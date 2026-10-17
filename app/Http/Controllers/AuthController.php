<?php

namespace App\Http\Controllers;

use Flux\Flux;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // login
    public function login()
    {
        return view('pages.auth.login');
    }

    public function verif(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if(Auth::attempt(['username' => $request->username, 'password' => $request->password, 'status_aktif' => true])){
            if (Auth::user()->peran === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Selamat Datang Admin');
            } else{
                return redirect()->route('kasir.dashboard')->with('success', 'Selamat Datang ' . Auth::user()->nama);
            }
        } else {
            return back()->with('error', 'Usename atau password salah.')->withInput();
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Kamu berhasil keluar.');
    }
}

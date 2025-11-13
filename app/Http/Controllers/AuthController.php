<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
        $errors = $validator->errors()->all();
        $errorString = implode('<br>', $errors);
        return back()->with('gagal', $errorString)->withInput();
        }
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {   
            $user = Auth::user();
        // Hapus semua session user ini (selain session yang sedang login)
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', Session::getId())
                ->delete();

            $request->session()->regenerate();
            return redirect()->route('dashboard.index')->with('sukses', 'Login berhasil.');
        } else {
            return back()->with('gagal', 'Email atau password salah.')->withInput();
        }
        
        
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login')->with('sukses', 'Anda telah berhasil logout.');
    }
    public function passwordForm()
    {
        return view('auth.password');
    }
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'current_password.min' => 'Password saat ini minimal 6 karakter.',
            'new_password.required' => 'Password baru harus diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorString = implode('<br>', $errors);
            return back()->with('gagal', $errorString)->withInput();
        }
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('gagal', 'Password saat ini salah.')->withInput(); 
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        return redirect()->route('dashboard.index')->with('sukses', 'Password berhasil diubah.');
    }
}

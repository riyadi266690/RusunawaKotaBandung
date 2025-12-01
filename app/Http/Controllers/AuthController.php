<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use function Symfony\Component\Clock\now;

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
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        // cek login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Email atau password salah'], 401)
                : back()->with('gagal', 'Email atau password salah')->withInput();
        }

        $user = Auth::user();
        if ($request->expectsJson()) {
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            $user->tokens()->latest()->first()->update([
                'expires_at' => Carbon::now()->addMinute(30)
            ]);

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token
            ]);
        }

        $request->session()->regenerate();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', Session::getId())
            ->delete();
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // roken
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ], 200);
        }

        // session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil (session)'
        ], 200);
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
            return response()->json([
                'gagal' => $errorString
            ]);
        }
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'gagal' => 'Password saat ini salah'
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json([
            'sukses' => 'Password berhasil diubah'
        ]);
    }
}

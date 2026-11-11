<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Process Register user
     */
    public function registerUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'required|max:255|unique:users,username',
            'password' => 'required|min:6|confirmed', 
            'nama_lengkap' => 'required|max:255',
            'tgl_lahir' => 'required',
            'bio' => 'sometimes|nullable',
            'pekerjaan' => 'sometimes|nullable|max:255',
            'domisili' => 'sometimes|nullable',
        ], [
            'email.unique' => 'Email ini sudah terdaftar, silakan login.',
            'username.unique' => 'Username ini sudah terdaftar, silakan login.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'required' => 'Kolom ini wajib diisi dan tidak boleh kosong.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter.',
        ]);

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password), // Memastikan password ter-hash dengan aman
            'nama_lengkap' => $request->nama_lengkap,
            'tgl_lahir' => $request->tgl_lahir,
            'bio' => $request->whenFilled('bio', fn($value) => $value),
            'pekerjaan' => $request->whenFilled('pekerjaan', fn($value) => $value),
            'domisili' => $request->whenFilled('domisili', fn($value) => $value),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registrasi Anda berhasil!',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    /**
     * Process Register (Admin)
     */
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'username' => 'required|max:255|unique:admins,username',
            'password' => 'required|min:6|confirmed', 
        ], [
            'email.unique' => 'Email ini sudah terdaftar, silakan login.',
            'username.unique' => 'Username ini sudah terdaftar, silakan login.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'required' => 'Kolom ini wajib diisi dan tidak boleh kosong.',
            'max' => 'Maksimal 255 karakter.',
        ]);

        $admin = Admin::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        $token = $admin->createToken('api')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registrasi Admin berhasil!',
            'data' => [
                'admin' => $admin,
                'token' => $token
            ]
        ], 201);
    }

    /**
     * Process Login user
     */
    public function loginUser(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', 
            'password' => 'required',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        //  Cari data user di database 
        $user = User::where($loginType, $credentials['login'])->first();

        // Handling user belum registrasi
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum registrasi, silakan registrasi terlebih dahulu.'
            ], 404);
        }

        // Jika user ada, baru cek apakah password-nya cocok
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password yang Anda masukkan salah.'
            ], 401);
        }

        // Jika semua benar, generate token login
        $token = $user->createToken('api')->plainTextToken;
        
        return response()->json([
            'status' => true,
            'message' => 'Login berhasil!',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ], 200);
    }

    /**
     * Process Login (Admin)
     */
    public function loginAdmin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', 
            'password' => 'required',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari data admin di database terlebih dahulu
        $admin = Admin::where($loginType, $credentials['login'])->first();

        // Handling jika data admin TIDAK ADA sama sekali
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Anda belum registrasi, silakan registrasi terlebih dahulu.'
            ], 404);
        }

        // cek apakah password-nya cocok
        if (!Hash::check($credentials['password'], $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password yang Anda masukkan salah.'
            ], 401);
        }

        $token = $admin->createToken('api')->plainTextToken;
        
        return response()->json([
            'status' => true,
            'message' => 'Login Admin berhasil!',
            'data' => [
                'token' => $token,
                'admin' => $admin
            ]
        ], 200);
    }

    /**
     * Process Logout user
     */
    public function logoutUser(Request $request) 
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Anda berhasil logout.'
        ], 200);
    }

    /**
     * Process Logout (Admin)
     */
    public function logoutAdmin(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Anda berhasil logout.'
        ], 200);
    }
}
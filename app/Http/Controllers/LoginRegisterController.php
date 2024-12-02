<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class LoginRegisterController extends Controller
{
    public function __construct()
    {
        // Hanya pengguna yang login bisa mengakses fungsi ini
        $this->middleware('auth')->only(['showProfile', 'editProfile', 'updateProfile', 'logout', 'dashboard']);

        // Hanya pengguna yang belum login (guest) bisa mengakses fungsi ini
        $this->middleware('guest')->only(['register', 'login', 'authenticate', 'forgotPassword', 'resetPassword']);

        // Fungsi dashboard hanya untuk admin
        $this->middleware('isAdmin')->only(['dashboard']); // Buat middleware isAdmin dengan 'php artisan make:middleware IsAdmin' lalu jangan lupa tambahkan di kernel.php
    }

    public function register()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png|max:1999'
        ]);

        if ($request->hasFile('photo')) {
            $filenameWithExt = $request->file('photo')->getClientOriginalName();
            $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filenameSimpan = $filenameWithoutExt . '_' . time() . '.' . $extension;

            $path = $request->file('photo')->storeAs('avada_kedavra', $filenameSimpan);
        } else {
            $path = 'noimage.png';
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'photo' => $path
        ]);

        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();

        return redirect()->route('profile.show')->withSuccess('You have successfully registered & logged in!');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        // Tambahkan validasi untuk email dan password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Ambil kredensial yang sudah divalidasi
        $credentials = $request->only('email', 'password');

        // Coba autentikasi menggunakan kredensial
        if (Auth::attempt($credentials)) {
            // Regenerasi sesi untuk keamanan
            $request->session()->regenerate();

            // Redirect ke halaman dashboard
            return redirect()->route('profile.show');
        }

        // Jika gagal, berikan pesan kesalahan
        return back()->with('error', 'Email atau password salah')->withInput($request->only('email'));
    }

    public function showProfile()
    {
        $userData = User::findOrFail(auth()->user()->id);

        return view('auth.profile', compact('userData'));
    }

    public function editProfile()
    {
        // Ambil data pengguna berdasarkan id
        $userData = User::findOrFail(auth()->user()->id);

        // Tampilkan view edit dengan data pengguna
        return view('auth.edit', compact('userData'));
    }

    public function updateProfile(Request $request)
    {
        // Validasi file
        $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users,email,' . auth()->user()->id,
            'photo' => 'nullable|image|mimes:jpeg,png|max:1999',
        ]);

        // Ambil pengguna yang sedang login
        $user = auth()->user();

        // Periksa apakah file diunggah
        if ($request->hasFile('photo')) {
            // Generate nama file unik
            $filenameWithExt = $request->file('photo')->getClientOriginalName();
            $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filenameSimpan = $filenameWithoutExt . '_' . time() . '.' . $extension;

            // Simpan file
            $path = $request->file('photo')->storeAs('avada_kedavra', $filenameSimpan);

            // Hapus file photo lama jika ada
            if ($user->photo && $user->photo !== 'noimage.png') {
                File::delete(public_path() . '/storage/' . $user->photo);
            }

            // Simpan nama file ke database
            $user->photo = $filenameSimpan;
        } else {
            if ($user->photo !== 'noimage.png') {
                File::delete(public_path() . '/storage/' . $user->photo);
                $path = 'noimage.png';
            } else {
                $path = 'noimage.png';
            }
        }

        // Ambil data pengguna berdasarkan ID
        $userData = User::findOrFail(auth()->user()->id);
        // Update data pengguna dengan data yang baru
        $updated = $userData->update([
            'name' => $request->name,
            'email' => $request->email,
            'photo' => $path,
        ]);

        if ($updated) {
            return redirect()->route('profile.show')->with('success', 'Profile berhasil diperbarui.');
        } else {
            return redirect()->route('profile.show')->with('error', 'Gagal memperbarui profile.');
        }
    }

    public function dashboard()
    {
        return view('auth.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withSuccess('You have logged out successfully!');;
    }

    public function forgotPassword()
    {
        return view('auth.reset');
    }

    public function resetPassword(Request $request)
    {
        // Validasi email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak ditemukan di sistem kami.',
        ]);

        // Kirim tautan reset password
        try {
            $status = Password::sendResetLink(['email' => $request->input('email')]);

            // Cek status pengiriman
            if ($status === Password::RESET_LINK_SENT) {
                return redirect()->route('login')->with('message', 'Tautan reset password telah dikirim ke email Anda!');
            }

            // Jika gagal, tampilkan pesan error
            return redirect()->back()->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan pada server
            return redirect()->back()->withErrors([
                'email' => 'Gagal mengirim tautan reset password. Silakan coba lagi nanti.',
            ]);
        }
    }
}

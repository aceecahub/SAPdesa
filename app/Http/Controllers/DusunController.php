<?php

namespace App\Http\Controllers;

use App\Models\Dusun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class DusunController extends Controller
{
    public function index()
    {
        $dusun = Dusun::with('users')->latest()->paginate(10);
        return Inertia::render('Master/Dusun/Index', [
            'dusun' => $dusun,
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Dusun/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:dusun,nama',
            'jumlah_penduduk' => 'nullable|integer|min:0',
            'nama_kepala' => 'nullable|string|max:255',
            'foto_kepala' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = $request->only(['nama', 'jumlah_penduduk', 'nama_kepala']);

        if ($request->hasFile('foto_kepala')) {
            $data['foto_kepala'] = $request->file('foto_kepala')->store('dusun', 'public');
        }

        $dusun = Dusun::create($data);

        // Otomatis Buat Akun User Kepala Dusun
        $namaKepala = $request->nama_kepala ?: ('Kepala ' . $dusun->nama);
        User::create([
            'name' => $namaKepala,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_plain' => Crypt::encryptString($request->password),
            'role' => 'kepala_dusun',
            'dusun_id' => $dusun->id,
            'rw_id' => null,
        ]);

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun dan Akun Kepala Dusun berhasil ditambahkan.');
    }

    public function show(Dusun $dusun)
    {
        $userAccount = User::where('role', 'kepala_dusun')->where('dusun_id', $dusun->id)->first();
        return Inertia::render('Master/Dusun/Show', [
            'dusun' => $dusun,
            'userAccount' => $userAccount,
        ]);
    }

    public function edit(Dusun $dusun)
    {
        $userAccount = User::where('role', 'kepala_dusun')->where('dusun_id', $dusun->id)->first();
        return Inertia::render('Master/Dusun/Edit', [
            'dusun' => $dusun,
            'userAccount' => $userAccount,
        ]);
    }

    public function update(Request $request, Dusun $dusun)
    {
        $userAccount = User::where('role', 'kepala_dusun')->where('dusun_id', $dusun->id)->first();
        $userId = $userAccount ? $userAccount->id : null;

        $request->validate([
            'nama' => 'required|string|max:255|unique:dusun,nama,' . $dusun->id,
            'jumlah_penduduk' => 'nullable|integer|min:0',
            'nama_kepala' => 'nullable|string|max:255',
            'foto_kepala' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?: 'NULL'),
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = $request->only(['nama', 'jumlah_penduduk', 'nama_kepala']);

        if ($request->hasFile('foto_kepala')) {
            if ($dusun->foto_kepala && Storage::disk('public')->exists($dusun->foto_kepala)) {
                Storage::disk('public')->delete($dusun->foto_kepala);
            }
            $data['foto_kepala'] = $request->file('foto_kepala')->store('dusun', 'public');
        }

        $dusun->update($data);

        // Update atau buat akun user baru jika belum ada
        $namaKepala = $request->nama_kepala ?: ('Kepala ' . $dusun->nama);
        if ($userAccount) {
            $userData = [
                'name' => $namaKepala,
                'email' => $request->email,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
                $userData['password_plain'] = Crypt::encryptString($request->password);
            }
            $userAccount->update($userData);
        } else {
            $password = $request->password ?: 'password123';
            User::create([
                'name' => $namaKepala,
                'email' => $request->email,
                'password' => Hash::make($password),
                'password_plain' => Crypt::encryptString($password),
                'role' => 'kepala_dusun',
                'dusun_id' => $dusun->id,
                'rw_id' => null,
            ]);
        }

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun dan Akun Kepala Dusun berhasil diperbarui.');
    }

    public function destroy(Dusun $dusun)
    {
        if ($dusun->foto_kepala && Storage::disk('public')->exists($dusun->foto_kepala)) {
            Storage::disk('public')->delete($dusun->foto_kepala);
        }

        // Hapus akun Kepala Dusun terkait
        User::where('role', 'kepala_dusun')->where('dusun_id', $dusun->id)->delete();

        $dusun->delete();

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun dan Akun terkait berhasil dihapus.');
    }
}
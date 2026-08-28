<?php

namespace App\Http\Controllers;

use App\Models\RW;
use App\Models\Dusun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RwController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RW::with(['dusun', 'users']);

        // Scope: kepala_dusun hanya bisa melihat RW di dusunnya
        if ($user->isKepalaDusun()) {
            $query->where('dusun_id', $user->dusun_id);
        } elseif ($request->filled('dusun_id')) {
            $query->where('dusun_id', $request->dusun_id);
        }

        $rwList = $query->orderBy('dusun_id')->orderBy('nomor')->paginate(15)->withQueryString();
        $dusunList = Dusun::all();

        return Inertia::render('Master/Rw/Index', [
            'rwList' => $rwList,
            'dusunList' => $dusunList,
            'filters' => $request->only(['dusun_id']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isKepalaDusun()) {
            abort(403, 'Kepala Dusun tidak memiliki akses untuk menambah RW baru.');
        }

        return Inertia::render('Master/Rw/Create', [
            'dusunList' => Dusun::all(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isKepalaDusun()) {
            abort(403, 'Kepala Dusun tidak memiliki akses untuk menambah RW baru.');
        }

        $request->validate([
            'dusun_id' => 'required|exists:dusun,id',
            'nomor' => 'required|integer|min:1|max:99',
            'nama_ketua' => 'nullable|string|max:255',
            'foto_ketua' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $exists = RW::where('dusun_id', $request->dusun_id)
            ->where('nomor', $request->nomor)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nomor' => 'Nomor RW sudah ada di dusun yang dipilih.']);
        }

        $data = [
            'dusun_id' => $request->dusun_id,
            'nomor' => $request->nomor,
            'nama_ketua' => $request->nama_ketua,
        ];

        if ($request->hasFile('foto_ketua')) {
            $data['foto_ketua'] = $request->file('foto_ketua')->store('rw', 'public');
        }

        $rw = RW::create($data);

        // Otomatis Buat Akun User Ketua RW
        $namaKetua = $request->nama_ketua ?: ('Ketua RW ' . str_pad($rw->nomor, 2, '0', STR_PAD_LEFT));
        User::create([
            'name' => $namaKetua,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_plain' => Crypt::encryptString($request->password),
            'role' => 'ketua_rw',
            'dusun_id' => null,
            'rw_id' => $rw->id,
        ]);

        return redirect()->route('master.rw.index')
            ->with('success', 'Data RW dan Akun Ketua RW berhasil ditambahkan.');
    }

    public function edit(RW $rw)
    {
        $user = Auth::user();
        if ($user->isKepalaDusun() && $rw->dusun_id !== $user->dusun_id) {
            abort(403, 'Anda tidak berhak mengedit data RW di luar dusun Anda.');
        }

        $userAccount = User::where('role', 'ketua_rw')->where('rw_id', $rw->id)->first();

        return Inertia::render('Master/Rw/Edit', [
            'rw' => $rw->load('dusun'),
            'dusunList' => Dusun::all(),
            'userAccount' => $userAccount,
        ]);
    }

    public function update(Request $request, RW $rw)
    {
        $user = Auth::user();
        if ($user->isKepalaDusun() && $rw->dusun_id !== $user->dusun_id) {
            abort(403, 'Anda tidak berhak mengedit data RW di luar dusun Anda.');
        }

        $dusunId = $user->isKepalaDusun() ? $user->dusun_id : $request->dusun_id;

        $userAccount = User::where('role', 'ketua_rw')->where('rw_id', $rw->id)->first();
        $userId = $userAccount ? $userAccount->id : null;

        $request->validate([
            'dusun_id' => 'required|exists:dusun,id',
            'nomor' => 'required|integer|min:1|max:99',
            'nama_ketua' => 'nullable|string|max:255',
            'foto_ketua' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?: 'NULL'),
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $exists = RW::where('dusun_id', $dusunId)
            ->where('nomor', $request->nomor)
            ->where('id', '!=', $rw->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nomor' => 'Nomor RW sudah ada di dusun yang dipilih.']);
        }

        $data = [
            'dusun_id' => $dusunId,
            'nomor' => $request->nomor,
            'nama_ketua' => $request->nama_ketua,
        ];

        if ($request->hasFile('foto_ketua')) {
            if ($rw->foto_ketua && Storage::disk('public')->exists($rw->foto_ketua)) {
                Storage::disk('public')->delete($rw->foto_ketua);
            }
            $data['foto_ketua'] = $request->file('foto_ketua')->store('rw', 'public');
        }

        $rw->update($data);

        // Update atau buat akun Ketua RW baru
        $namaKetua = $request->nama_ketua ?: ('Ketua RW ' . str_pad($rw->nomor, 2, '0', STR_PAD_LEFT));
        if ($userAccount) {
            $userData = [
                'name' => $namaKetua,
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
                'name' => $namaKetua,
                'email' => $request->email,
                'password' => Hash::make($password),
                'password_plain' => Crypt::encryptString($password),
                'role' => 'ketua_rw',
                'dusun_id' => null,
                'rw_id' => $rw->id,
            ]);
        }

        return redirect()->route('master.rw.index')
            ->with('success', 'Data RW dan Akun Ketua RW berhasil diperbarui.');
    }

    public function destroy(RW $rw)
    {
        $user = Auth::user();
        if ($user->isKepalaDusun()) {
            abort(403, 'Kepala Dusun tidak diizinkan menghapus data RW.');
        }

        if ($rw->foto_ketua && Storage::disk('public')->exists($rw->foto_ketua)) {
            Storage::disk('public')->delete($rw->foto_ketua);
        }

        // Hapus akun Ketua RW terkait
        User::where('role', 'ketua_rw')->where('rw_id', $rw->id)->delete();

        $rw->delete();

        return redirect()->route('master.rw.index')
            ->with('success', 'Data RW dan Akun terkait berhasil dihapus.');
    }
}

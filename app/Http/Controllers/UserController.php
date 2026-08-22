<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dusun;
use App\Models\RW;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['dusun', 'rw.dusun'])->latest()->paginate(15);

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('User/Create', [
            'dusunList' => Dusun::all(),
            'rwList' => RW::with('dusun')->orderBy('dusun_id')->orderBy('nomor')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:superadmin,admin,kepala_dusun,ketua_rw',
            'dusun_id' => 'nullable|exists:dusun,id',
            'rw_id' => 'nullable|exists:rw,id',
        ]);

        if ($request->role === 'kepala_dusun' && !$request->dusun_id) {
            return back()->withErrors(['dusun_id' => 'Kepala Dusun wajib memilih dusun.']);
        }

        if ($request->role === 'ketua_rw' && !$request->rw_id) {
            return back()->withErrors(['rw_id' => 'Ketua RW wajib memilih RW.']);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'dusun_id' => $request->role === 'kepala_dusun' ? $request->dusun_id : null,
            'rw_id' => $request->role === 'ketua_rw' ? $request->rw_id : null,
        ]);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return Inertia::render('User/Edit', [
            'userEdit' => $user->load(['dusun', 'rw.dusun']),
            'dusunList' => Dusun::all(),
            'rwList' => RW::with('dusun')->orderBy('dusun_id')->orderBy('nomor')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:superadmin,admin,kepala_dusun,ketua_rw',
            'dusun_id' => 'nullable|exists:dusun,id',
            'rw_id' => 'nullable|exists:rw,id',
        ]);

        if ($request->role === 'kepala_dusun' && !$request->dusun_id) {
            return back()->withErrors(['dusun_id' => 'Kepala Dusun wajib memilih dusun.']);
        }

        if ($request->role === 'ketua_rw' && !$request->rw_id) {
            return back()->withErrors(['rw_id' => 'Ketua RW wajib memilih RW.']);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'dusun_id' => $request->role === 'kepala_dusun' ? $request->dusun_id : null,
            'rw_id' => $request->role === 'ketua_rw' ? $request->rw_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}

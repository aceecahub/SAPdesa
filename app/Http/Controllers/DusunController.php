<?php

namespace App\Http\Controllers;

use App\Models\Dusun;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DusunController extends Controller
{
    public function index()
    {
        $dusun = Dusun::latest()->paginate(10);
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
        ]);

        Dusun::create($request->only(['nama', 'jumlah_penduduk', 'nama_kepala']));

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun berhasil ditambahkan.');
    }

    public function show(Dusun $dusun)
    {
        return Inertia::render('Master/Dusun/Show', [
            'dusun' => $dusun,
        ]);
    }

    public function edit(Dusun $dusun)
    {
        return Inertia::render('Master/Dusun/Edit', [
            'dusun' => $dusun,
        ]);
    }

    public function update(Request $request, Dusun $dusun)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:dusun,nama,' . $dusun->id,
            'jumlah_penduduk' => 'nullable|integer|min:0',
            'nama_kepala' => 'nullable|string|max:255',
        ]);

        $dusun->update($request->only(['nama', 'jumlah_penduduk', 'nama_kepala']));

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun berhasil diperbarui.');
    }

    public function destroy(Dusun $dusun)
    {
        $dusun->delete();

        return redirect()->route('master.dusun.index')
            ->with('success', 'Dusun berhasil dihapus.');
    }
}
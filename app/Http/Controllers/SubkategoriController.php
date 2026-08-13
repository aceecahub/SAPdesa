<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Subkategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubkategoriController extends Controller
{
    public function index()
    {
        $subkategori = Subkategori::with('kategori')->latest()->paginate(10);
        return Inertia::render('Master/Subkategori/Index', [
            'subkategori' => $subkategori,
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Subkategori/Create', [
            'kategoriList' => Kategori::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subkategori,slug',
        ]);

        Subkategori::create($request->only(['kategori_id', 'nama', 'slug']));

        return redirect()->route('master.subkategori.index')
            ->with('success', 'Subkategori berhasil ditambahkan.');
    }

    public function edit(Subkategori $subkategori)
    {
        return Inertia::render('Master/Subkategori/Edit', [
            'subkategori' => $subkategori,
            'kategoriList' => Kategori::all(),
        ]);
    }

    public function update(Request $request, Subkategori $subkategori)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subkategori,slug,' . $subkategori->id,
        ]);

        $subkategori->update($request->only(['kategori_id', 'nama', 'slug']));

        return redirect()->route('master.subkategori.index')
            ->with('success', 'Subkategori berhasil diperbarui.');
    }

    public function destroy(Subkategori $subkategori)
    {
        $subkategori->delete();

        return redirect()->route('master.subkategori.index')
            ->with('success', 'Subkategori berhasil dihapus.');
    }
}

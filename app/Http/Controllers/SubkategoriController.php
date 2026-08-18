<?php

namespace App\Http\Controllers;

use App\Models\Subkategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubkategoriController extends Controller
{
    public function index()
    {
        $subkategori = Subkategori::latest()->paginate(10);
        return Inertia::render('Master/Subkategori/Index', [
            'subkategori' => $subkategori,
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Subkategori/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subkategori,slug',
        ]);

        Subkategori::create($request->only(['nama', 'slug']));

        return redirect()->route('master.subkategori.index')
            ->with('success', 'Subkategori berhasil ditambahkan.');
    }

    public function edit(Subkategori $subkategori)
    {
        return Inertia::render('Master/Subkategori/Edit', [
            'subkategori' => $subkategori,
        ]);
    }

    public function update(Request $request, Subkategori $subkategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subkategori,slug,' . $subkategori->id,
        ]);

        $subkategori->update($request->only(['nama', 'slug']));

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

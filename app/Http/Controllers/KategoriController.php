<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::latest()->paginate(10);
        return Inertia::render('Master/Kategori/Index', [
            'kategori' => $kategori,
        ]);
    }

    public function create()
    {
        return Inertia::render('Master/Kategori/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kategori,slug',
        ]);

        Kategori::create($request->only(['nama', 'slug']));

        return redirect()->route('master.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Kategori $kategori)
    {
        return Inertia::render('Master/Kategori/Show', [
            'kategori' => $kategori,
        ]);
    }

    public function edit(Kategori $kategori)
    {
        return Inertia::render('Master/Kategori/Edit', [
            'kategori' => $kategori,
        ]);
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:kategori,slug,' . $kategori->id,
        ]);

        $kategori->update($request->only(['nama', 'slug']));

        return redirect()->route('master.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('master.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
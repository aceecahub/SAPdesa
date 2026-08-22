<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Kategori;
use App\Models\Subkategori;
use App\Models\Dusun;
use App\Models\RW;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Laporan::with(['kategori', 'subkategori', 'rw.dusun', 'user']);

        // Scope berdasarkan role
        if ($user->isKetuaRw()) {
            $query->where('rw_id', $user->rw_id);
        } elseif ($user->isKepalaDusun()) {
            $query->whereHas('rw', function ($q) use ($user) {
                $q->where('dusun_id', $user->dusun_id);
            });
        }

        // Filter RW & Dusun
        if ($request->filled('rw_id')) {
            $query->where('rw_id', $request->rw_id);
        }
        if ($request->filled('dusun_id')) {
            $query->whereHas('rw', function ($q) use ($request) {
                $q->where('dusun_id', $request->dusun_id);
            });
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $laporan = $query->latest('tanggal')->paginate(15)->withQueryString();
        $kategoriList = Kategori::all();
        $dusunList = Dusun::all();
        $rwList = RW::with('dusun')->orderBy('dusun_id')->orderBy('nomor')->get();

        return Inertia::render('Laporan/Index', [
            'laporan' => $laporan,
            'kategoriList' => $kategoriList,
            'dusunList' => $dusunList,
            'rwList' => $rwList,
            'filters' => $request->only(['rw_id', 'dusun_id', 'kategori_id', 'status', 'search']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Siapa saja yang boleh buat laporan: ketua_rw, kepala_dusun, admin, superadmin
        if (!$user->isKetuaRw() && !$user->isKepalaDusun() && !$user->isAdmin() && !$user->isSuperadmin()) {
            abort(403);
        }

        $rwListQuery = RW::with('dusun');
        if ($user->isKepalaDusun()) {
            $rwListQuery->where('dusun_id', $user->dusun_id);
        } elseif ($user->isKetuaRw()) {
            $rwListQuery->where('id', $user->rw_id);
        }

        return Inertia::render('Laporan/Create', [
            'kategoriList' => Kategori::all(),
            'subkategoriList' => Subkategori::all(),
            'rwList' => $rwListQuery->get(),
            'dusunList' => Dusun::all(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'subkategori_id' => 'nullable|exists:subkategori,id',
            'lampiran' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $data = $request->only(['judul', 'isi', 'kategori_id', 'subkategori_id']);
        $data['user_id'] = $user->id;
        $data['tanggal'] = now()->toDateString();
        $data['status'] = 'baru';

        // Set RW ID
        if ($user->isKetuaRw()) {
            $data['rw_id'] = $user->rw_id;
        } else {
            $request->validate(['rw_id' => 'required|exists:rw,id']);
            if ($user->isKepalaDusun()) {
                $rwObj = RW::find($request->rw_id);
                if (!$rwObj || $rwObj->dusun_id !== $user->dusun_id) {
                    return back()->withErrors(['rw_id' => 'RW yang dipilih harus berada di wilayah Dusun Anda.']);
                }
            }
            $data['rw_id'] = $request->rw_id;
        }

        // Lampiran
        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        $laporan = Laporan::create($data);

        // Tindak lanjut: pencatatan awal
        TindakLanjut::create([
            'laporan_id' => $laporan->id,
            'keterangan' => 'Laporan dibuat oleh ' . $user->name,
            'user_id' => $user->id,
        ]);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dibuat.');
    }

    public function show(Laporan $laporan)
    {
        $user = Auth::user();
        $laporan->load(['kategori', 'subkategori', 'rw.dusun', 'user', 'tindakLanjut.user']);

        // Scope check
        if ($user->isKetuaRw() && $laporan->rw_id !== $user->rw_id) {
            abort(403, 'Anda tidak berhak mengakses laporan ini.');
        }

        if ($user->isKepalaDusun() && $laporan->rw?->dusun_id !== $user->dusun_id) {
            abort(403, 'Anda tidak berhak mengakses laporan ini.');
        }

        return Inertia::render('Laporan/Show', [
            'laporan' => $laporan,
        ]);
    }

    public function edit(Laporan $laporan)
    {
        $user = Auth::user();

        // Hanya pembuat laporan yang bisa mengedit kontennya
        if ($laporan->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengedit laporan ini.');
        }

        // Hanya bisa edit jika status masih baru
        if ($laporan->status !== 'baru') {
            return redirect()->route('laporan.show', $laporan)
                ->with('error', 'Laporan hanya bisa diedit saat status masih Baru.');
        }

        return Inertia::render('Laporan/Edit', [
            'laporan' => $laporan,
            'kategoriList' => Kategori::all(),
            'subkategoriList' => Subkategori::all(),
        ]);
    }

    public function update(Request $request, Laporan $laporan)
    {
        $user = Auth::user();

        // Hanya pembuat laporan yang bisa mengedit kontennya
        if ($laporan->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengedit laporan ini.');
        }

        if ($laporan->status !== 'baru') {
            return redirect()->route('laporan.show', $laporan)
                ->with('error', 'Laporan hanya bisa diedit saat status masih Baru.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori_id' => 'required|exists:kategori,id',
            'subkategori_id' => 'nullable|exists:subkategori,id',
            'lampiran' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $data = $request->only(['judul', 'isi', 'kategori_id', 'subkategori_id']);

        if ($request->hasFile('lampiran')) {
            // Delete old lampiran
            if ($laporan->lampiran) {
                Storage::disk('public')->delete($laporan->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        $laporan->update($data);

        return redirect()->route('laporan.show', $laporan)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(Laporan $laporan)
    {
        $user = Auth::user();

        if ($user->isKetuaRw()) {
            if ($laporan->rw_id !== $user->rw_id || $laporan->status !== 'baru') {
                abort(403, 'Anda tidak berhak menghapus laporan ini.');
            }
        } elseif ($user->isKepalaDusun()) {
            if ($laporan->rw?->dusun_id !== $user->dusun_id || $laporan->status !== 'baru') {
                abort(403, 'Anda tidak berhak menghapus laporan ini.');
            }
        }

        $laporan->delete();

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    public function updateStatus(Request $request, Laporan $laporan)
    {
        $user = Auth::user();

        // Hanya admin/superadmin yang bisa ubah status
        if ($user->isKetuaRw() || $user->isKepalaDusun()) {
            abort(403, 'Anda tidak berhak mengubah status laporan.');
        }

        $request->validate([
            'status' => 'required|in:baru,diproses,selesai,ditolak',
            'respon_admin' => 'nullable|string',
        ]);

        $oldStatus = $laporan->status;
        $newStatus = $request->status;

        // Cek hirarki status: tidak boleh kembali ke tahap sebelumnya
        $statusOrder = [
            'baru' => 1,
            'diproses' => 2,
            'selesai' => 3,
            'ditolak' => 3,
        ];

        $oldOrder = $statusOrder[$oldStatus] ?? 1;
        $newOrder = $statusOrder[$newStatus] ?? 1;

        if ($newOrder < $oldOrder || ($oldOrder === 3 && $newStatus !== $oldStatus)) {
            return redirect()->back()->withErrors([
                'status' => "Status yang sudah berada pada tahap \"{$oldStatus}\" tidak dapat dikembalikan ke tahap sebelumnya."
            ]);
        }

        $laporan->update([
            'status' => $newStatus,
            'respon_admin' => $request->respon_admin ?? $laporan->respon_admin,
        ]);

        // Catat tindak lanjut
        $keterangan = "Status diubah dari \"{$oldStatus}\" ke \"{$newStatus}\" oleh {$user->name}";
        if ($request->filled('respon_admin')) {
            $keterangan .= ". Respon: " . $request->respon_admin;
        }

        TindakLanjut::create([
            'laporan_id' => $laporan->id,
            'keterangan' => $keterangan,
            'user_id' => $user->id,
        ]);

        return redirect()->route('laporan.show', $laporan)
            ->with('success', "Status laporan berhasil diubah ke \"{$newStatus}\".");
    }
}

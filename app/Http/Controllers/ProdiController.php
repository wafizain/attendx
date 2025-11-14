<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProdiExport;
use App\Imports\ProdiImport;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prodi::with(['kaprodi', 'mahasiswa', 'kelas', 'mataKuliah']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by jenjang
        if ($request->filled('jenjang')) {
            $query->jenjang($request->jenjang);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->aktif();
            } elseif ($request->status == '0') {
                $query->nonaktif();
            }
        }

        // Filter by akreditasi
        if ($request->filled('akreditasi')) {
            $query->where('akreditasi', $request->akreditasi);
        }

        // Sort (default: oldest first by created_at)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $prodis = $query->paginate($perPage)->withQueryString();

        // Get dosen list for kaprodi dropdown
        $dosenList = User::where('role', 'dosen')->where('status', 'aktif')->get();

        return view('admin.prodi.index', compact('prodis', 'dosenList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.prodi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:16|unique:program_studi,kode',
            'nama' => 'required|string|min:3|max:150',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'fakultas' => 'nullable|string|max:100',
            'akreditasi' => 'nullable|in:A,B,C,Baik,Baik Sekali,Unggul',
            'kaprodi_user_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
            'deskripsi' => 'nullable|string',
            'kode_eksternal' => 'nullable|string|max:32',
            'email_kontak' => 'nullable|email|max:150',
            'telepon_kontak' => 'nullable|string|max:32',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->nama);

        // Filter hanya kolom yang ada di tabel untuk mencegah error kolom tidak ditemukan
        $allowed = [
            'kode','nama','jenjang','akreditasi','status','kaprodi_user_id','slug',
            'deskripsi','kode_eksternal','email_kontak','telepon_kontak','fakultas'
        ];
        $filtered = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data) && Schema::hasColumn('program_studi', $key)) {
                $filtered[$key] = $data[$key];
            }
        }

        $prodi = Prodi::create($filtered);

        LogHelper::create(auth()->id(), 'prodi', "Menambahkan program studi: {$prodi->nama} ({$prodi->kode})");

        return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $prodi = Prodi::with(['kaprodi', 'mahasiswa', 'kelas.mataKuliah', 'mataKuliah', 'dosen'])
            ->withCount(['mahasiswa', 'kelas', 'mataKuliah'])
            ->findOrFail($id);

        // Get statistik detail
        $statistik = [
            'total_mahasiswa' => $prodi->mahasiswa()->count(),
            'mahasiswa_aktif' => $prodi->mahasiswaAktif()->count(),
            'total_kelas' => $prodi->kelas()->count(),
            'kelas_aktif' => $prodi->kelas()->where('status', 1)->count(),
            'total_mata_kuliah' => $prodi->mataKuliah()->count(),
            'total_dosen' => $prodi->dosenKelas()->count(), // Menggunakan relasi yang lebih sederhana
        ];

        return view('admin.prodi.show', compact('prodi', 'statistik'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $prodi = Prodi::findOrFail($id);
        return view('admin.prodi.edit', compact('prodi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            'kode' => 'required|string|max:16|unique:program_studi,kode,' . $id,
            'nama' => 'required|string|min:3|max:150',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'fakultas' => 'nullable|string|max:100',
            'akreditasi' => 'nullable|in:A,B,C,Baik,Baik Sekali,Unggul',
            'kaprodi_user_id' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
            'deskripsi' => 'nullable|string',
            'kode_eksternal' => 'nullable|string|max:32',
            'email_kontak' => 'nullable|email|max:150',
            'telepon_kontak' => 'nullable|string|max:32',
        ]);

        $data = $request->all();
        if ($request->filled('nama') && $prodi->nama != $request->nama) {
            $data['slug'] = Str::slug($request->nama);
        }

        // Filter kolom yang ada di tabel
        $allowed = [
            'kode','nama','jenjang','akreditasi','status','kaprodi_user_id','slug',
            'deskripsi','kode_eksternal','email_kontak','telepon_kontak','fakultas'
        ];
        $filtered = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data) && Schema::hasColumn('program_studi', $key)) {
                $filtered[$key] = $data[$key];
            }
        }

        $prodi->update($filtered);

        LogHelper::update(auth()->id(), 'prodi', "Mengupdate program studi: {$prodi->nama} ({$prodi->kode})");

        return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        $prodi = Prodi::findOrFail($id);
        
        // Cek apakah ada relasi aktif
        if ($prodi->mahasiswa()->count() > 0 || $prodi->kelas()->count() > 0) {
            return redirect()->back()->with('error', 'Program Studi tidak dapat dihapus karena masih memiliki mahasiswa atau kelas terdaftar. Nonaktifkan terlebih dahulu.');
        }

        $namaProdi = $prodi->nama;
        $prodi->delete(); // Soft delete

        LogHelper::delete(auth()->id(), 'prodi', "Menghapus program studi: {$namaProdi} ({$prodi->kode})");

        return redirect()->route('prodi.index')->with('success', 'Program Studi berhasil dihapus.');
    }

    /**
     * Toggle status (aktif/nonaktif)
     */
    public function toggleStatus(string $id)
    {
        $prodi = Prodi::findOrFail($id);
        $prodi->status = !$prodi->status;
        $prodi->save();

        $statusText = $prodi->status ? 'diaktifkan' : 'dinonaktifkan';
        LogHelper::update(auth()->id(), 'prodi', "Program studi {$prodi->nama} {$statusText}");

        return redirect()->back()->with('success', "Program Studi berhasil {$statusText}.");
    }

    /**
     * Rotate Kaprodi
     */
    public function rotateKaprodi(Request $request, string $id)
    {
        $prodi = Prodi::findOrFail($id);

        $request->validate([
            'kaprodi_user_id' => 'required|exists:users,id',
        ]);

        $oldKaprodi = $prodi->kaprodi ? $prodi->kaprodi->name : 'Tidak ada';
        $prodi->kaprodi_user_id = $request->kaprodi_user_id;
        $prodi->save();

        // Refresh the relationship to get the new kaprodi
        $prodi->refresh();
        $newKaprodi = $prodi->kaprodi ? $prodi->kaprodi->name : 'Tidak ada';

        LogHelper::update(auth()->id(), 'prodi', "Rotate Kaprodi {$prodi->nama}: {$oldKaprodi} → {$newKaprodi}");

        return redirect()->back()->with('success', 'Kaprodi berhasil diubah.');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'selected_ids' => 'required|array|min:1',
            'selected_ids.*' => 'exists:program_studi,id',
        ]);

        $ids = $request->selected_ids;

        // Only delete action is allowed
        Prodi::whereIn('id', $ids)->delete();
        LogHelper::create(auth()->id(), 'prodi', "Bulk delete " . count($ids) . " program studi");
        return redirect()->back()->with('success', count($ids) . ' Program Studi berhasil dihapus.');
    }

    /**
     * Import from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            Excel::import(new ProdiImport, $request->file('file'));

            LogHelper::create(auth()->id(), 'prodi', "Import program studi dari CSV");

            return redirect()->back()->with('success', 'Program Studi berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Export to CSV/XLSX
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $query = Prodi::with(['kaprodi']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('jenjang')) {
            $query->jenjang($request->jenjang);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prodis = $query->get();

        LogHelper::create(auth()->id(), 'prodi', "Export " . $prodis->count() . " program studi ke {$format}");

        $filename = 'prodi_' . date('YmdHis') . '.' . $format;
        return Excel::download(new ProdiExport($prodis), $filename);
    }

    /**
     * Download template import
     */
    public function downloadTemplate()
    {
        $headers = [
            'kode',
            'nama',
            'jenjang',
            'akreditasi',
            'status'
        ];

        $filename = 'template_import_prodi.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        fputcsv($handle, $headers);
        fputcsv($handle, ['IF-01', 'Teknik Informatika', 'S1', 'A', '1']);
        fclose($handle);
        $content = ob_get_clean();

        LogHelper::create(auth()->id(), 'prodi', "Download template import prodi");

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

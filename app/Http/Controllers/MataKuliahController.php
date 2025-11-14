<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    /**
     * Display a listing of mata kuliah.
     */
    public function index()
    {
        $mataKuliah = MataKuliah::orderBy('kode_mk', 'asc')->paginate(20);
        return view('admin.akademik.mata-kuliah.index', compact('mataKuliah'));
    }

    /**
     * Show the form for creating a new mata kuliah.
     */
    public function create()
    {
        return view('admin.akademik.mata-kuliah.create');
    }

    /**
     * Store a newly created mata kuliah.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_mk' => 'required|unique:mata_kuliah,kode_mk|max:20',
            'nama_mk' => 'required|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'semester' => 'nullable|integer|min:1|max:8',
            'deskripsi' => 'nullable',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $mataKuliah = MataKuliah::create($request->all());

        // Log aktivitas
        LogHelper::create('mata_kuliah', 'Menambahkan mata kuliah: ' . $mataKuliah->nama_mk, [
            'mata_kuliah_id' => $mataKuliah->id,
            'kode_mk' => $mataKuliah->kode_mk,
        ]);

        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * Display the specified mata kuliah.
     */
    public function show($id)
    {
        $mataKuliah = MataKuliah::with(['kelas.dosen', 'kelas.mahasiswa'])->findOrFail($id);
        return view('admin.akademik.mata-kuliah.show', compact('mataKuliah'));
    }

    /**
     * Show the form for editing mata kuliah.
     */
    public function edit($id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        return view('admin.akademik.mata-kuliah.edit', compact('mataKuliah'));
    }

    /**
     * Update the specified mata kuliah.
     */
    public function update(Request $request, $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        $request->validate([
            'kode_mk' => 'required|max:20|unique:mata_kuliah,kode_mk,' . $id,
            'nama_mk' => 'required|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'semester' => 'nullable|integer|min:1|max:8',
            'deskripsi' => 'nullable',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $mataKuliah->update($request->all());

        // Log aktivitas
        LogHelper::update('mata_kuliah', 'Mengupdate mata kuliah: ' . $mataKuliah->nama_mk, [
            'mata_kuliah_id' => $mataKuliah->id,
            'kode_mk' => $mataKuliah->kode_mk,
        ]);

        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil diupdate.');
    }

    /**
     * Remove the specified mata kuliah.
     */
    public function destroy($id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        $namaMk = $mataKuliah->nama_mk;

        // Cek apakah ada kelas yang menggunakan mata kuliah ini
        if ($mataKuliah->kelas()->count() > 0) {
            return redirect()->route('mata-kuliah.index')
                ->with('error', 'Mata kuliah tidak dapat dihapus karena masih digunakan oleh kelas.');
        }

        $mataKuliah->delete();

        // Log aktivitas
        LogHelper::delete('mata_kuliah', 'Menghapus mata kuliah: ' . $namaMk, [
            'mata_kuliah_id' => $id,
        ]);

        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}

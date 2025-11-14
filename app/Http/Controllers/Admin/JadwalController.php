<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKelas;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;

class JadwalController extends Controller
{
    /**
     * Display a listing of jadwal
     */
    public function index(Request $request)
    {
        $query = JadwalKelas::with(['kelas.mataKuliah', 'kelas.dosen']);

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $jadwals = $query->orderBy('hari')->orderBy('waktu_mulai')->get();
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();

        return view('admin.jadwal.index', compact('jadwals', 'kelasList'));
    }

    /**
     * Show the form for creating a new jadwal
     */
    public function create()
    {
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();
        return view('admin.jadwal.create', compact('kelasList'));
    }

    /**
     * Store a newly created jadwal
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan' => 'nullable|string|max:50',
        ]);

        // Cek bentrok jadwal
        $bentrok = JadwalKelas::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where(function($q) use ($request) {
                $q->whereBetween('waktu_mulai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhereBetween('waktu_selesai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('waktu_mulai', '<=', $request->waktu_mulai)
                         ->where('waktu_selesai', '>=', $request->waktu_selesai);
                  });
            })
            ->exists();

        if ($bentrok) {
            return redirect()->back()->withInput()->with('error', 'Jadwal bentrok dengan jadwal yang sudah ada.');
        }

        $jadwal = JadwalKelas::create($request->all());

        LogHelper::create(auth()->id(), 'jadwal', "Menambahkan jadwal kuliah");

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Show the form for editing jadwal
     */
    public function edit(string $id)
    {
        $jadwal = JadwalKelas::findOrFail($id);
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();
        return view('admin.jadwal.edit', compact('jadwal', 'kelasList'));
    }

    /**
     * Update jadwal
     */
    public function update(Request $request, string $id)
    {
        $jadwal = JadwalKelas::findOrFail($id);

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan' => 'nullable|string|max:50',
        ]);

        // Cek bentrok jadwal (exclude jadwal ini)
        $bentrok = JadwalKelas::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $id)
            ->where(function($q) use ($request) {
                $q->whereBetween('waktu_mulai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhereBetween('waktu_selesai', [$request->waktu_mulai, $request->waktu_selesai])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('waktu_mulai', '<=', $request->waktu_mulai)
                         ->where('waktu_selesai', '>=', $request->waktu_selesai);
                  });
            })
            ->exists();

        if ($bentrok) {
            return redirect()->back()->withInput()->with('error', 'Jadwal bentrok dengan jadwal yang sudah ada.');
        }

        $jadwal->update($request->all());

        LogHelper::update(auth()->id(), 'jadwal', "Mengupdate jadwal kuliah");

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diupdate.');
    }

    /**
     * Remove jadwal
     */
    public function destroy(string $id)
    {
        $jadwal = JadwalKelas::findOrFail($id);
        $jadwal->delete();

        LogHelper::delete(auth()->id(), 'jadwal', "Menghapus jadwal kuliah");

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}

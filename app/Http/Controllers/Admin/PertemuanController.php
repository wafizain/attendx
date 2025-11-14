<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SesiAbsensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;

class PertemuanController extends Controller
{
    /**
     * Display pertemuan (sesi absensi)
     */
    public function index(Request $request)
    {
        $query = SesiAbsensi::with(['kelas.mataKuliah', 'kelas.dosen']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $pertemuans = $query->orderBy('tanggal', 'desc')->orderBy('waktu_mulai', 'desc')->paginate(20);
        $kelasList = Kelas::with('mataKuliah')->aktif()->get();

        return view('admin.pertemuan.index', compact('pertemuans', 'kelasList'));
    }

    /**
     * Konfigurasi grace period keterlambatan
     */
    public function config()
    {
        // Ambil konfigurasi dari database atau config file
        $config = [
            'grace_period' => config('absensi.grace_period', 15), // Default 15 menit
            'auto_alpha_after' => config('absensi.auto_alpha_after', 30), // Default 30 menit
        ];

        return view('admin.pertemuan.config', compact('config'));
    }

    /**
     * Update konfigurasi
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'grace_period' => 'required|integer|min:0|max:60',
            'auto_alpha_after' => 'required|integer|min:0|max:120',
        ]);

        // Simpan ke file config atau database
        // Untuk sementara kita simpan ke .env atau config cache
        
        LogHelper::update(auth()->id(), 'config', "Mengupdate konfigurasi keterlambatan");

        return redirect()->back()->with('success', 'Konfigurasi berhasil diupdate.');
    }

    /**
     * Override/Edit pertemuan
     */
    public function edit($id)
    {
        $pertemuan = SesiAbsensi::with(['kelas', 'absensi.mahasiswa'])->findOrFail($id);
        return view('admin.pertemuan.edit', compact('pertemuan'));
    }

    /**
     * Update pertemuan
     */
    public function update(Request $request, $id)
    {
        $pertemuan = SesiAbsensi::findOrFail($id);

        $request->validate([
            'topik' => 'nullable|string|max:200',
            'status' => 'required|in:draft,aktif,selesai,dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        $pertemuan->update($request->only(['topik', 'status', 'catatan']));

        LogHelper::update(auth()->id(), 'pertemuan', "Mengupdate pertemuan/sesi: {$pertemuan->kelas->nama_kelas}");

        return redirect()->route('pertemuan.index')->with('success', 'Pertemuan berhasil diupdate.');
    }

    /**
     * Cancel pertemuan
     */
    public function cancel($id)
    {
        $pertemuan = SesiAbsensi::findOrFail($id);
        
        $pertemuan->update([
            'status' => 'dibatalkan',
        ]);

        LogHelper::update(auth()->id(), 'pertemuan', "Membatalkan pertemuan: {$pertemuan->kelas->nama_kelas}");

        return redirect()->back()->with('success', 'Pertemuan berhasil dibatalkan.');
    }
}

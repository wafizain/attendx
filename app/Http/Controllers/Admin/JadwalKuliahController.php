<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\MataKuliah;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Ruangan;
use App\Models\Mahasiswa;
use App\Models\KelasMember;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalKuliahController extends Controller
{
    /**
     * Display a listing of jadwal
     */
    public function index(Request $request)
    {
        $query = JadwalKuliah::with(['mataKuliah', 'dosen', 'kelas', 'ruangan']);

        // Filter by dosen
        if ($request->filled('dosen')) {
            $query->where('id_dosen', $request->dosen);
        }

        // Filter by mata kuliah
        if ($request->filled('mk')) {
            $query->where('id_mk', $request->mk);
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('id_kelas', $request->kelas);
        }

        // Filter by hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Filter by ruangan
        if ($request->filled('ruangan')) {
            $query->where('id_ruangan', $request->ruangan);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $query->orderBy('hari', 'asc')->orderBy('jam_mulai', 'asc');

        $jadwalList = $query->paginate(20)->withQueryString();

        // Data untuk filter
        $dosenList = User::where('role', 'dosen')->where('status', 'aktif')->orderBy('name')->get();
        $mkList = MataKuliah::orderBy('nama_mk')->get();
        $ruanganList = Ruangan::aktif()->orderBy('kode')->get();
        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();

        return view('admin.jadwal.index', compact(
            'jadwalList',
            'dosenList',
            'mkList',
            'ruanganList',
            'kelasList'
        ));
    }

    /**
     * Show the form for creating a new jadwal
     */
    public function create()
    {
        $mkList = MataKuliah::orderBy('nama_mk')->get();
        $dosenList = User::where('role', 'dosen')->where('status', 'aktif')->orderBy('name')->get();
        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();
        $ruanganList = Ruangan::aktif()->orderBy('kode')->get();
        $semesterList = \App\Models\Semester::orderByLatest()->get();

        return view('admin.jadwal.create', compact('mkList', 'dosenList', 'kelasList', 'ruanganList', 'semesterList'));
    }

    /**
     * Store a newly created jadwal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mk' => 'required|exists:mata_kuliah,id',
            'id_dosen' => 'required|exists:users,id',
            'id_kelas' => 'nullable|exists:kelas,id',
            'id_ruangan' => 'required|exists:ruangan,id',
            'semester_id' => 'required|exists:semesters,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'absen_open_min' => 'nullable|integer|min:0|max:60',
            'absen_close_min' => 'nullable|integer|min:0|max:120',
            'grace_late_min' => 'nullable|integer|min:0|max:60',
            'wajah_wajib' => 'nullable|boolean',
            'catatan' => 'nullable|string',
        ]);

        // Set default values for absensi rules if not provided
        $validated['absen_open_min'] = $validated['absen_open_min'] ?? 10;
        $validated['absen_close_min'] = $validated['absen_close_min'] ?? 30;
        $validated['grace_late_min'] = $validated['grace_late_min'] ?? 15;
        $validated['wajah_wajib'] = $request->has('wajah_wajib') ? 1 : 0;
        $validated['status'] = 'aktif';

        // Simple conflict check for same semester, day, time, and room
        $existingJadwal = JadwalKuliah::where('semester_id', $validated['semester_id'])
            ->where('hari', $validated['hari'])
            ->where('id_ruangan', $validated['id_ruangan'])
            ->where('status', 'aktif')
            ->where(function($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                      ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                      });
            })
            ->exists();

        if ($existingJadwal) {
            return back()->withErrors([
                'id_ruangan' => 'Ruangan sudah digunakan pada hari dan waktu yang sama di semester ini.'
            ])->withInput();
        }

        // Get semester data for tanggal_mulai and tanggal_selesai
        $semester = \App\Models\Semester::findOrFail($validated['semester_id']);

        DB::beginTransaction();
        try {
            $jadwal = JadwalKuliah::create([
                'id_mk' => $validated['id_mk'],
                'id_dosen' => $validated['id_dosen'],
                'id_kelas' => $validated['id_kelas'],
                'id_ruangan' => $validated['id_ruangan'],
                'semester_id' => $validated['semester_id'],
                'hari' => $validated['hari'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
                'tanggal_mulai' => $semester->tanggal_mulai,
                'tanggal_selesai' => $semester->tanggal_selesai,
                'absen_open_min' => $validated['absen_open_min'],
                'absen_close_min' => $validated['absen_close_min'],
                'grace_late_min' => $validated['grace_late_min'],
                'wajah_wajib' => $validated['wajah_wajib'],
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            LogHelper::create(auth()->id(), 'Jadwal Kuliah', 'Jadwal kuliah baru dibuat: ' . $jadwal->mataKuliah->nama_mk);

            DB::commit();

            return redirect()->route('jadwal.show', $jadwal->id)
                ->with('success', 'Jadwal kuliah berhasil dibuat dan pertemuan telah digenerate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat jadwal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified jadwal
     */
    public function show($id)
    {
        $jadwal = JadwalKuliah::with([
            'mataKuliah',
            'dosen',
            'kelas',
            'ruangan',
            'mahasiswa',
            'pertemuan' => function($q) {
                $q->orderBy('minggu_ke');
            }
        ])->findOrFail($id);

        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();

        return view('admin.jadwal.show', compact('jadwal', 'kelasList'));
    }

    /**
     * Show the form for editing the specified jadwal
     */
    public function edit($id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);
        $mkList = MataKuliah::orderBy('nama_mk')->get();
        $dosenList = User::where('role', 'dosen')->where('status', 'aktif')->orderBy('name')->get();
        $kelasList = Kelas::where('status', 1)->orderBy('kode')->get();
        $ruanganList = Ruangan::aktif()->orderBy('kode')->get();

        return view('admin.jadwal.edit', compact('jadwal', 'mkList', 'dosenList', 'kelasList', 'ruanganList'));
    }

    /**
     * Update the specified jadwal
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);

        $validated = $request->validate([
            'id_mk' => 'required|exists:mata_kuliah,id',
            'id_dosen' => 'required|exists:users,id',
            'id_kelas' => 'nullable|exists:kelas,id',
            'id_ruangan' => 'required|exists:ruangan,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'absen_open_min' => 'nullable|integer|min:0|max:60',
            'absen_close_min' => 'nullable|integer|min:0|max:120',
            'grace_late_min' => 'nullable|integer|min:0|max:60',
            'wajah_wajib' => 'nullable|boolean',
            'status' => 'required|in:aktif,nonaktif',
            'catatan' => 'nullable|string',
        ]);

        // Check room conflict (exclude current jadwal)
        if (JadwalKuliah::hasRoomConflict(
            $validated['id_ruangan'],
            $validated['hari'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $id
        )) {
            return back()->withErrors([
                'id_ruangan' => 'Ruangan sudah digunakan pada hari dan waktu yang sama.'
            ])->withInput();
        }

        // Check lecturer conflict (exclude current jadwal)
        if (JadwalKuliah::hasLecturerConflict(
            $validated['id_dosen'],
            $validated['hari'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $id
        )) {
            return back()->withErrors([
                'id_dosen' => 'Dosen sudah mengajar pada hari dan waktu yang sama.'
            ])->withInput();
        }

        $jadwal->update($validated);

        LogHelper::update(auth()->id(), 'Jadwal Kuliah', 'Jadwal kuliah diupdate: ' . $jadwal->mataKuliah->nama_mk);

        return redirect()->route('jadwal.show', $jadwal->id)
            ->with('success', 'Jadwal kuliah berhasil diupdate.');
    }

    /**
     * Remove the specified jadwal
     */
    public function destroy($id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);
        $namaJadwal = $jadwal->mataKuliah->nama_mk;

        $jadwal->delete();

        LogHelper::delete(auth()->id(), 'Jadwal Kuliah', 'Jadwal kuliah dihapus: ' . $namaJadwal);

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal kuliah berhasil dihapus.');
    }

    /**
     * Enroll mahasiswa to jadwal
     */
    public function enrollMahasiswa(Request $request, $id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);

        $validated = $request->validate([
            'mahasiswa_ids' => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswa,id',
        ]);

        $enrolled = 0;
        foreach ($validated['mahasiswa_ids'] as $mahasiswaId) {
            if (!$jadwal->mahasiswa()->where('id_mahasiswa', $mahasiswaId)->exists()) {
                $jadwal->mahasiswa()->attach($mahasiswaId, ['tanggal_daftar' => now()]);
                $enrolled++;
            }
        }

        LogHelper::create(auth()->id(), 'Jadwal Kuliah', "Enroll {$enrolled} mahasiswa ke jadwal: " . $jadwal->mataKuliah->nama_mk);

        return back()->with('success', "{$enrolled} mahasiswa berhasil didaftarkan ke jadwal.");
    }

    /**
     * Enroll all active members of a class to the jadwal
     */
    public function enrollKelas(Request $request, $id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        // Get active members (by NIM) then map to Mahasiswa IDs
        $nims = KelasMember::where('id_kelas', $validated['kelas_id'])
            ->aktif()
            ->pluck('nim');

        if ($nims->isEmpty()) {
            return back()->with('error', 'Kelas tidak memiliki anggota aktif.');
        }

        $mahasiswaIds = Mahasiswa::whereIn('nim', $nims)->pluck('id');

        DB::beginTransaction();
        try {
            // Ensure this jadwal is assigned to the selected class (one jadwal -> one kelas)
            if ($jadwal->id_kelas !== (int) $validated['kelas_id']) {
                $jadwal->id_kelas = $validated['kelas_id'];
                $jadwal->save();
            }

            $enrolled = 0;
            foreach ($mahasiswaIds as $mhsId) {
                if (!$jadwal->mahasiswa()->where('id_mahasiswa', $mhsId)->exists()) {
                    $jadwal->mahasiswa()->attach($mhsId, ['tanggal_daftar' => now()]);
                    $enrolled++;
                }
            }

            LogHelper::create(auth()->id(), 'Jadwal Kuliah', "Enroll {$enrolled} mahasiswa (dari kelas) ke jadwal: " . $jadwal->mataKuliah->nama_mk);

            DB::commit();

            return back()->with('success', "Kelas diset ke jadwal dan {$enrolled} mahasiswa aktif berhasil didaftarkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan dari kelas: ' . $e->getMessage());
        }
    }

    /**
     * Remove mahasiswa from jadwal
     */
    public function removeMahasiswa($id, $mahasiswaId)
    {
        $jadwal = JadwalKuliah::findOrFail($id);
        $jadwal->mahasiswa()->detach($mahasiswaId);

        LogHelper::delete(auth()->id(), 'Jadwal Kuliah', "Mahasiswa dikeluarkan dari jadwal: " . $jadwal->mataKuliah->nama_mk);

        return back()->with('success', 'Mahasiswa berhasil dikeluarkan dari jadwal.');
    }
}

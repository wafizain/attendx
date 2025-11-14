<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\User;
use App\Models\JadwalKelas;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * Display a listing of kelas.
     */
    public function index(Request $request)
    {
        // Query dengan join untuk mendapatkan data lengkap
        $query = DB::table('kelas')
            ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->join('users', 'kelas.dosen_id', '=', 'users.id')
            ->leftJoin('dosen', 'users.id', '=', 'dosen.id_user')
            ->select(
                'kelas.*',
                'mata_kuliah.kode_mk',
                'mata_kuliah.nama_mk',
                'mata_kuliah.sks',
                'users.name as dosen_name',
                'dosen.nidn',
                'dosen.gelar',
                DB::raw('(SELECT COUNT(*) FROM kelas_mahasiswa WHERE kelas_mahasiswa.kelas_id = kelas.id) as jumlah_mahasiswa')
            );

        // Filter
        if ($request->filled('mata_kuliah_id')) {
            $query->where('kelas.mata_kuliah_id', $request->mata_kuliah_id);
        }
        if ($request->filled('tahun_ajaran')) {
            $query->where('kelas.tahun_ajaran', $request->tahun_ajaran);
        }
        if ($request->filled('semester')) {
            $query->where('kelas.semester', $request->semester);
        }
        if ($request->filled('status')) {
            $query->where('kelas.status', $request->status);
        }

        $kelas = $query->orderBy('kelas.tahun_ajaran', 'desc')
            ->orderBy('kelas.semester', 'desc')
            ->orderBy('mata_kuliah.nama_mk', 'asc')
            ->paginate(20);

        // Data untuk filter
        $mataKuliahList = MataKuliah::orderBy('nama_mk')->get();
        $tahunAjaranList = DB::table('kelas')
            ->select('tahun_ajaran')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');

        return view('admin.akademik.kelas.index', compact('kelas', 'mataKuliahList', 'tahunAjaranList'));
    }

    /**
     * Show the form for creating a new kelas.
     */
    public function create()
    {
        $mataKuliahList = MataKuliah::where('status', 'aktif')->orderBy('nama_mk')->get();
        
        // Ambil dosen dengan data lengkap
        $dosenList = DB::table('users')
            ->join('dosen', 'users.id', '=', 'dosen.id_user')
            ->where('users.role', 'dosen')
            ->where('users.status', 'aktif')
            ->select('users.id', 'users.name', 'dosen.nidn', 'dosen.gelar', 'dosen.prodi')
            ->orderBy('users.name')
            ->get();
        
        return view('admin.akademik.kelas.create', compact('mataKuliahList', 'dosenList'));
    }

    /**
     * Store a newly created kelas.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'dosen_id' => 'required|exists:users,id',
            'nama_kelas' => 'required|max:255',
            'tahun_ajaran' => 'required|max:20',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|max:255',
            'kapasitas' => 'required|integer|min:1|max:200',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        DB::beginTransaction();
        try {
            $kelas = Kelas::create($validated);

            // Ambil data untuk log
            $mataKuliah = MataKuliah::find($validated['mata_kuliah_id']);
            $dosen = User::find($validated['dosen_id']);

            // Log aktivitas
            LogHelper::create(
                auth()->id(), 
                'Kelola Kelas', 
                'Menambahkan kelas: ' . $kelas->nama_kelas . ' - ' . $mataKuliah->nama_mk . ' (Dosen: ' . $dosen->name . ')'
            );

            DB::commit();

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan kelas: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified kelas.
     */
    public function show($id)
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen', 'mahasiswa', 'jadwal', 'sesiAbsensi'])
            ->findOrFail($id);
        
        return view('admin.akademik.kelas.show', compact('kelas'));
    }

    /**
     * Show the form for editing kelas.
     */
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $mataKuliahList = MataKuliah::where('status', 'aktif')->orderBy('nama_mk')->get();
        
        // Ambil dosen dengan data lengkap
        $dosenList = DB::table('users')
            ->join('dosen', 'users.id', '=', 'dosen.id_user')
            ->where('users.role', 'dosen')
            ->where('users.status', 'aktif')
            ->select('users.id', 'users.name', 'dosen.nidn', 'dosen.gelar', 'dosen.prodi')
            ->orderBy('users.name')
            ->get();
        
        return view('admin.akademik.kelas.edit', compact('kelas', 'mataKuliahList', 'dosenList'));
    }

    /**
     * Update the specified kelas.
     */
    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'dosen_id' => 'required|exists:users,id',
            'nama_kelas' => 'required|max:255',
            'tahun_ajaran' => 'required|max:20',
            'semester' => 'required|in:ganjil,genap',
            'ruangan' => 'nullable|max:255',
            'kapasitas' => 'required|integer|min:1|max:200',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        DB::beginTransaction();
        try {
            $kelas->update($validated);

            // Ambil data untuk log
            $mataKuliah = MataKuliah::find($validated['mata_kuliah_id']);
            $dosen = User::find($validated['dosen_id']);

            // Log aktivitas
            LogHelper::update(
                auth()->id(), 
                'Kelola Kelas', 
                'Mengupdate kelas: ' . $kelas->nama_kelas . ' - ' . $mataKuliah->nama_mk . ' (Dosen: ' . $dosen->name . ')'
            );

            DB::commit();

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate kelas: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified kelas.
     */
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Cek apakah ada mahasiswa di kelas
        $jumlahMahasiswa = DB::table('kelas_mahasiswa')->where('kelas_id', $id)->count();
        if ($jumlahMahasiswa > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kelas yang masih memiliki mahasiswa. Hapus mahasiswa terlebih dahulu.');
        }

        // Cek apakah ada sesi absensi
        $jumlahSesi = DB::table('sesi_absensi')->where('kelas_id', $id)->count();
        if ($jumlahSesi > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus kelas yang sudah memiliki sesi absensi.');
        }

        DB::beginTransaction();
        try {
            $namaKelas = $kelas->nama_kelas;
            $mataKuliah = $kelas->mataKuliah->nama_mk;

            // Hapus jadwal kelas terlebih dahulu
            DB::table('jadwal_kelas')->where('kelas_id', $id)->delete();

            // Hapus kelas
            $kelas->delete();

            // Log aktivitas
            LogHelper::delete(
                auth()->id(), 
                'Kelola Kelas', 
                'Menghapus kelas: ' . $namaKelas . ' - ' . $mataKuliah
            );

            DB::commit();

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    /**
     * Manage mahasiswa in kelas
     */
    public function mahasiswa($id)
    {
        $kelas = Kelas::with(['mahasiswa', 'mataKuliah', 'dosen'])->findOrFail($id);
        
        // Ambil mahasiswa yang tersedia (belum terdaftar di kelas ini) dengan data lengkap
        $mahasiswaIds = $kelas->mahasiswa->pluck('id')->toArray();
        $availableMahasiswa = DB::table('users')
            ->join('mahasiswa', 'users.id', '=', 'mahasiswa.id_user')
            ->where('users.role', 'mahasiswa')
            ->where('users.status', 'aktif')
            ->whereNotIn('users.id', $mahasiswaIds)
            ->select('users.id', 'users.name', 'mahasiswa.nim', 'mahasiswa.kelas', 'mahasiswa.prodi', 'mahasiswa.angkatan')
            ->orderBy('mahasiswa.nim')
            ->get();

        return view('admin.akademik.kelas.mahasiswa', compact('kelas', 'availableMahasiswa'));
    }

    /**
     * Add mahasiswa to kelas
     */
    public function addMahasiswa(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'mahasiswa_id' => 'required|array',
            'mahasiswa_id.*' => 'exists:users,id',
        ]);

        // Cek kapasitas kelas
        $jumlahMahasiswaSaatIni = DB::table('kelas_mahasiswa')->where('kelas_id', $id)->count();
        $jumlahMahasiswaBaru = count($validated['mahasiswa_id']);
        
        if (($jumlahMahasiswaSaatIni + $jumlahMahasiswaBaru) > $kelas->kapasitas) {
            return redirect()->back()->with('error', 'Kapasitas kelas tidak mencukupi. Sisa kapasitas: ' . ($kelas->kapasitas - $jumlahMahasiswaSaatIni));
        }

        DB::beginTransaction();
        try {
            foreach ($validated['mahasiswa_id'] as $mahasiswaId) {
                // Cek apakah sudah terdaftar
                $exists = DB::table('kelas_mahasiswa')
                    ->where('kelas_id', $id)
                    ->where('mahasiswa_id', $mahasiswaId)
                    ->exists();
                
                if (!$exists) {
                    $kelas->mahasiswa()->attach($mahasiswaId, [
                        'tanggal_bergabung' => now(),
                        'status' => 'aktif',
                    ]);
                }
            }

            // Log aktivitas
            LogHelper::create(
                auth()->id(), 
                'Kelola Kelas', 
                'Menambahkan ' . $jumlahMahasiswaBaru . ' mahasiswa ke kelas: ' . $kelas->nama_kelas
            );

            DB::commit();

            return redirect()->route('kelas.mahasiswa', $id)
                ->with('success', 'Mahasiswa berhasil ditambahkan ke kelas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Remove mahasiswa from kelas
     */
    public function removeMahasiswa($kelasId, $mahasiswaId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $mahasiswa = User::findOrFail($mahasiswaId);

        // Cek apakah mahasiswa memiliki absensi di kelas ini
        $hasAbsensi = DB::table('absensi')
            ->where('kelas_id', $kelasId)
            ->where('mahasiswa_id', $mahasiswaId)
            ->exists();
        
        if ($hasAbsensi) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus mahasiswa yang sudah memiliki data absensi.');
        }

        DB::beginTransaction();
        try {
            $kelas->mahasiswa()->detach($mahasiswaId);

            // Log aktivitas
            LogHelper::delete(
                auth()->id(), 
                'Kelola Kelas', 
                'Menghapus mahasiswa ' . $mahasiswa->name . ' dari kelas: ' . $kelas->nama_kelas
            );

            DB::commit();

            return redirect()->route('kelas.mahasiswa', $kelasId)
                ->with('success', 'Mahasiswa berhasil dihapus dari kelas.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Manage jadwal kelas
     */
    public function jadwal($id)
    {
        $kelas = Kelas::with(['jadwal', 'mataKuliah', 'dosen'])->findOrFail($id);
        return view('admin.akademik.kelas.jadwal', compact('kelas'));
    }

    /**
     * Store jadwal kelas
     */
    public function storeJadwal(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan' => 'nullable|max:255',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        // Cek bentrok jadwal (hari dan waktu yang sama di ruangan yang sama)
        if ($validated['ruangan']) {
            $bentrok = JadwalKelas::where('hari', $validated['hari'])
                ->where('ruangan', $validated['ruangan'])
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
            
            if ($bentrok) {
                return redirect()->back()->withInput()->with('error', 'Jadwal bentrok dengan jadwal lain di ruangan yang sama.');
            }
        }

        DB::beginTransaction();
        try {
            $jadwal = JadwalKelas::create([
                'kelas_id' => $id,
                'hari' => $validated['hari'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
                'ruangan' => $validated['ruangan'],
                'status' => $validated['status'],
            ]);

            // Log aktivitas
            LogHelper::create(
                auth()->id(), 
                'Kelola Jadwal', 
                'Menambahkan jadwal untuk kelas: ' . $kelas->nama_kelas . ' (' . $validated['hari'] . ', ' . $validated['jam_mulai'] . '-' . $validated['jam_selesai'] . ')'
            );

            DB::commit();

            return redirect()->route('kelas.jadwal', $id)
                ->with('success', 'Jadwal berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Delete jadwal kelas
     */
    public function deleteJadwal($kelasId, $jadwalId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $jadwal = JadwalKelas::findOrFail($jadwalId);

        DB::beginTransaction();
        try {
            $hari = $jadwal->hari;
            $waktu = $jadwal->jam_mulai . '-' . $jadwal->jam_selesai;
            
            $jadwal->delete();

            // Log aktivitas
            LogHelper::delete(
                auth()->id(), 
                'Kelola Jadwal', 
                'Menghapus jadwal kelas: ' . $kelas->nama_kelas . ' (' . $hari . ', ' . $waktu . ')'
            );

            DB::commit();

            return redirect()->route('kelas.jadwal', $kelasId)
                ->with('success', 'Jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
}

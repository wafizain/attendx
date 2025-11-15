<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();

        // Ambil kelas yang diikuti mahasiswa via relasi many-to-many
        $kelasList = Kelas::whereHas('mahasiswa', function($q) use ($mahasiswa) {
            $q->where('users.id', $mahasiswa->id);
        })->with(['mataKuliah', 'dosen'])
          ->orderBy('tahun_ajaran', 'desc')
          ->orderBy('semester', 'desc')
          ->get();

        return view('mahasiswa.kelas.index', compact('kelasList'));
    }
}

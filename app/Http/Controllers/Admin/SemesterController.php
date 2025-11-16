<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;

class SemesterController extends Controller
{
    /**
     * Display semester settings
     */
    public function index()
    {
        $semesters = Semester::orderByLatest()->get();
        
        return view('admin.settings.semester', compact('semesters'));
    }

    /**
     * Store new semester
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|integer|in:1,2',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jumlah_pertemuan' => 'required|integer|min:1|max:20',
            'pertemuan_uts' => 'nullable|integer|min:1|max:20',
            'pertemuan_uas' => 'nullable|integer|min:1|max:20',
        ]);

        // Validate UTS and UAS are within range
        if ($request->filled('pertemuan_uts') && $request->pertemuan_uts > $request->jumlah_pertemuan) {
            return back()->withErrors(['pertemuan_uts' => 'Pertemuan UTS tidak boleh melebihi jumlah pertemuan'])->withInput();
        }

        if ($request->filled('pertemuan_uas') && $request->pertemuan_uas > $request->jumlah_pertemuan) {
            return back()->withErrors(['pertemuan_uas' => 'Pertemuan UAS tidak boleh melebihi jumlah pertemuan'])->withInput();
        }

        $validated['status'] = 'tidak_aktif';

        $semester = Semester::create($validated);

        LogHelper::create(auth()->id(), 'semester', "Menambah semester: {$semester->tahun_ajaran} - {$semester->semester}");

        return redirect()->route('admin.semester.index')->with('success', 'Semester berhasil ditambahkan');
    }

    /**
     * Get semester data for edit
     */
    public function edit($id)
    {
        $semester = Semester::findOrFail($id);
        
        return response()->json($semester);
    }

    /**
     * Update semester
     */
    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        $validated = $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|integer|in:1,2',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jumlah_pertemuan' => 'required|integer|min:1|max:20',
            'pertemuan_uts' => 'nullable|integer|min:1|max:20',
            'pertemuan_uas' => 'nullable|integer|min:1|max:20',
        ]);

        // Validate UTS and UAS are within range
        if ($request->filled('pertemuan_uts') && $request->pertemuan_uts > $request->jumlah_pertemuan) {
            return back()->withErrors(['pertemuan_uts' => 'Pertemuan UTS tidak boleh melebihi jumlah pertemuan'])->withInput();
        }

        if ($request->filled('pertemuan_uas') && $request->pertemuan_uas > $request->jumlah_pertemuan) {
            return back()->withErrors(['pertemuan_uas' => 'Pertemuan UAS tidak boleh melebihi jumlah pertemuan'])->withInput();
        }

        $semester->update($validated);

        LogHelper::update(auth()->id(), 'semester', "Mengupdate semester: {$semester->tahun_ajaran} - {$semester->semester}");

        return redirect()->route('admin.semester.index')->with('success', 'Semester berhasil diupdate');
    }

    /**
     * Activate semester
     */
    public function activate($id)
    {
        // Deactivate all semesters
        Semester::where('status', 'aktif')->update(['status' => 'tidak_aktif']);

        // Activate selected semester
        $semester = Semester::findOrFail($id);
        $semester->update(['status' => 'aktif']);

        LogHelper::update(auth()->id(), 'semester', "Mengaktifkan semester: {$semester->tahun_ajaran} - {$semester->semester}");

        return redirect()->route('admin.semester.index')->with('success', 'Semester berhasil diaktifkan');
    }
}

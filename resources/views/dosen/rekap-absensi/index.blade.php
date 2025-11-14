@extends('layouts.master')

@section('title', 'Rekap Absensi Mahasiswa')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="fas fa-chart-line me-2"></i>
                Rekap Absensi Mahasiswa
            </h4>
            <p class="text-muted mb-0">Laporan kehadiran mahasiswa per semester</p>
        </div>
    </div>

    <!-- Filter Semester -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dosen.rekap-absensi.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" class="form-select" onchange="this.form.submit()">
                            @foreach($semesterList as $semester)
                            <option value="{{ $semester->id }}" 
                                    {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                {{ $semester->tahun_ajaran }} - Semester {{ $semester->semester }}
                                @if($semester->status == 'aktif') <span class="badge bg-success ms-1">Aktif</span> @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rekap Absensi Per Kelas -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Rekap Absensi Per Kelas
            </h6>
        </div>
        <div class="card-body">
            @forelse($rekapData as $rekap)
            <div class="border-bottom pb-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="bg-light rounded-circle p-3 text-center">
                                    <i class="fas fa-chalkboard-teacher fa-fw text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $rekap['jadwal']->mataKuliah->nama_mk }}</h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-auto">
                                        <small class="text-muted">Kelas:</small>
                                        <strong>{{ $rekap['jadwal']->kelas->nama ?? '-' }}</strong>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">Total Pertemuan:</small>
                                        <strong>{{ $rekap['total_pertemuan'] }}</strong>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">Total Mahasiswa:</small>
                                        <strong>{{ $rekap['total_mahasiswa'] }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>
                                        Hadir: {{ $rekap['statistik']['hadir'] }}
                                    </span>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Izin: {{ $rekap['statistik']['izin'] }}
                                    </span>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-heartbeat me-1"></i>
                                        Sakit: {{ $rekap['statistik']['sakit'] }}
                                    </span>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Alpha: {{ $rekap['statistik']['alpha'] }}
                                    </span>
                                    <span class="badge bg-info">
                                        <i class="fas fa-percentage me-1"></i>
                                        {{ $rekap['statistik']['persentase_kehadiran'] }}% Kehadiran
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <a href="{{ route('dosen.rekap-absensi.show', $rekap['jadwal']->id) }}?semester_id={{ $semesterId }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Detail Mahasiswa
                            </a>
                            <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $rekap['jadwal']->id, 'format' => 'pdf']) }}?semester_id={{ $semesterId }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $rekap['jadwal']->id, 'format' => 'excel']) }}?semester_id={{ $semesterId }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum Ada Data Rekap</h5>
                <p class="text-muted">Belum ada pertemuan yang selesai untuk semester ini</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

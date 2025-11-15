@extends('layouts.master')

@section('title', 'Rekap Absensi Mahasiswa')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title mb-1 fw-bold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Rekap Absensi Mahasiswa
                    </h4>
                    <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>Laporan kehadiran mahasiswa per semester</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Semester -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <form method="GET" action="{{ route('dosen.rekap-absensi.index') }}" id="filterForm">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>Filter Semester
                        </label>
                        <select name="semester_id" class="form-select form-select-lg" onchange="this.form.submit()">
                            @foreach($semesterList as $semester)
                            <option value="{{ $semester->id }}" 
                                    {{ $semesterId == $semester->id ? 'selected' : '' }}>
                                {{ $semester->tahun_ajaran }} - Semester {{ $semester->semester }}
                                @if($semester->status == 'aktif') (Aktif) @endif
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h5 class="mb-1 fw-bold">Total Mata Kuliah</h5>
                            <h2 class="mb-0 fw-bold">{{ count($rekapData) }}</h2>
                            <small class="opacity-75">Mata kuliah yang Anda ampu semester ini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekap Absensi Per Kelas -->
    <div class="row">
        @forelse($rekapData as $rekap)
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <!-- Informasi Mata Kuliah -->
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-book fa-2x text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold text-dark">{{ $rekap['jadwal']->mataKuliah->nama_mk }}</h5>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-users me-1"></i>{{ $rekap['jadwal']->kelas->nama ?? '-' }}
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-calendar-check me-1"></i>{{ $rekap['total_pertemuan'] }} Pertemuan
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-user-graduate me-1"></i>{{ $rekap['total_mahasiswa'] }} Mahasiswa
                                        </span>
                                    </div>
                                    <!-- Progress Bar Kehadiran -->
                                    <div class="mt-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted fw-semibold">Tingkat Kehadiran</small>
                                            <small class="fw-bold text-{{ $rekap['statistik']['persentase_kehadiran'] >= 75 ? 'success' : ($rekap['statistik']['persentase_kehadiran'] >= 50 ? 'warning' : 'danger') }}">
                                                {{ $rekap['statistik']['persentase_kehadiran'] }}%
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $rekap['statistik']['persentase_kehadiran'] >= 75 ? 'success' : ($rekap['statistik']['persentase_kehadiran'] >= 50 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $rekap['statistik']['persentase_kehadiran'] }}%"
                                                 aria-valuenow="{{ $rekap['statistik']['persentase_kehadiran'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Kehadiran -->
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-check-circle text-success mb-1"></i>
                                        <h4 class="mb-0 fw-bold text-success">{{ $rekap['statistik']['hadir'] }}</h4>
                                        <small class="text-muted">Hadir</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-file-alt text-warning mb-1"></i>
                                        <h4 class="mb-0 fw-bold text-warning">{{ $rekap['statistik']['izin'] }}</h4>
                                        <small class="text-muted">Izin</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-heartbeat text-danger mb-1"></i>
                                        <h4 class="mb-0 fw-bold text-danger">{{ $rekap['statistik']['sakit'] }}</h4>
                                        <small class="text-muted">Sakit</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-secondary bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-times-circle text-secondary mb-1"></i>
                                        <h4 class="mb-0 fw-bold text-secondary">{{ $rekap['statistik']['alpha'] }}</h4>
                                        <small class="text-muted">Alpha</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-lg-3 text-lg-end">
                            <div class="d-grid gap-2">
                                <a href="{{ route('dosen.rekap-absensi.show', $rekap['jadwal']->id) }}?semester_id={{ $semesterId }}" 
                                   class="btn btn-primary btn-lg">
                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                </a>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $rekap['jadwal']->id, 'format' => 'pdf']) }}?semester_id={{ $semesterId }}" 
                                       class="btn btn-outline-success"
                                       title="Download PDF">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </a>
                                    <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $rekap['jadwal']->id, 'format' => 'excel']) }}?semester_id={{ $semesterId }}" 
                                       class="btn btn-outline-success"
                                       title="Download Excel">
                                        <i class="fas fa-file-excel me-1"></i>Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted mb-2">Belum Ada Data Rekap</h5>
                    <p class="text-muted mb-0">Belum ada pertemuan yang selesai untuk semester ini.<br>Data akan muncul setelah Anda menyelesaikan pertemuan.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.transition-all {
    transition: all 0.3s ease;
}

.form-select-lg {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
}

.form-select-lg:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>
@endsection

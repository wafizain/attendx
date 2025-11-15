@extends('layouts.master')

@section('title', 'Detail Rekap Absensi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="page-title mb-1 fw-bold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Detail Rekap Absensi
                    </h4>
                    <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i>Rekap kehadiran per mahasiswa</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('dosen.rekap-absensi.index') }}?semester_id={{ $semesterId }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $jadwal->id, 'format' => 'pdf']) }}?semester_id={{ $semesterId }}" 
                           class="btn btn-success">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $jadwal->id, 'format' => 'excel']) }}?semester_id={{ $semesterId }}" 
                           class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Kelas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-2 text-center mb-3 mb-lg-0">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-4 d-inline-block">
                        <i class="fas fa-book fa-3x text-primary"></i>
                    </div>
                </div>
                <div class="col-lg-10">
                    <h4 class="fw-bold mb-3 text-dark">{{ $jadwal->mataKuliah->nama_mk }}</h4>
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="fas fa-users text-info"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Kelas</small>
                                    <strong>{{ $jadwal->kelas->nama ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="fas fa-calendar-check text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Pertemuan</small>
                                    <strong>{{ $pertemuanList->count() }}x</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="fas fa-user-graduate text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Mahasiswa</small>
                                    <strong>{{ $jadwal->mahasiswa->count() }} Orang</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="fas fa-clock text-danger"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Jadwal</small>
                                    <strong>{{ ucfirst($jadwal->hari) }}, {{ substr($jadwal->jam_mulai, 0, 5) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Keseluruhan -->
    @php
    $totalHadir = collect($rekapMahasiswa)->sum('hadir');
    $totalIzin = collect($rekapMahasiswa)->sum('izin');
    $totalSakit = collect($rekapMahasiswa)->sum('sakit');
    $totalAlpha = collect($rekapMahasiswa)->sum('alpha');
    $totalAll = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
    $avgPersentase = $totalAll > 0 ? round(($totalHadir / $totalAll) * 100, 2) : 0;
    @endphp
    
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-success">{{ $totalHadir }}</h3>
                    <small class="text-muted fw-semibold">Hadir</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-file-alt fa-lg text-warning"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-warning">{{ $totalIzin }}</h3>
                    <small class="text-muted fw-semibold">Izin</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-heartbeat fa-lg text-danger"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-danger">{{ $totalSakit }}</h3>
                    <small class="text-muted fw-semibold">Sakit</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-times-circle fa-lg text-secondary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-secondary">{{ $totalAlpha }}</h3>
                    <small class="text-muted fw-semibold">Alpha</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-list-ol fa-lg text-primary"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-primary">{{ $totalAll }}</h3>
                    <small class="text-muted fw-semibold">Total</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-3">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-percentage fa-lg text-info"></i>
                    </div>
                    <h3 class="mb-1 fw-bold text-info">{{ $avgPersentase }}%</h3>
                    <small class="text-muted fw-semibold">Rata-rata</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Mahasiswa -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Daftar Kehadiran Mahasiswa
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               id="searchMahasiswa" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Cari nama atau NIM mahasiswa...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableMahasiswa">
                    <thead class="bg-light">
                        <tr>
                            <th width="50" class="text-center">#</th>
                            <th class="sortable" data-sort="nim" style="cursor: pointer;">
                                NIM <i class="fas fa-sort ms-1 text-muted"></i>
                            </th>
                            <th class="sortable" data-sort="nama" style="cursor: pointer;">
                                Nama Mahasiswa <i class="fas fa-sort ms-1 text-muted"></i>
                            </th>
                            <th width="80" class="text-center sortable" data-sort="hadir" style="cursor: pointer;">
                                Hadir <i class="fas fa-sort ms-1 text-muted"></i>
                            </th>
                            <th width="80" class="text-center">Izin</th>
                            <th width="80" class="text-center">Sakit</th>
                            <th width="80" class="text-center">Alpha</th>
                            <th width="80" class="text-center">Total</th>
                            <th width="120" class="text-center sortable" data-sort="persentase" style="cursor: pointer;">
                                Persentase <i class="fas fa-sort ms-1 text-muted"></i>
                            </th>
                            <th width="120" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapMahasiswa as $index => $rekap)
                        <tr class="mahasiswa-row" 
                            data-nim="{{ strtolower($rekap['mahasiswa']->nim) }}" 
                            data-nama="{{ strtolower($rekap['mahasiswa']->nama) }}"
                            data-hadir="{{ $rekap['hadir'] }}"
                            data-persentase="{{ $rekap['persentase_kehadiran'] }}">
                            <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $rekap['mahasiswa']->nim }}</span></td>
                            <td>
                                <span class="fw-semibold">{{ $rekap['mahasiswa']->nama }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success fs-6 px-3 py-2">{{ $rekap['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning fs-6 px-3 py-2">{{ $rekap['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6 px-3 py-2">{{ $rekap['sakit'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary fs-6 px-3 py-2">{{ $rekap['alpha'] }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="fs-5">{{ $rekap['total'] }}</strong>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="progress w-100 mb-1" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $rekap['persentase_kehadiran'] >= 75 ? 'success' : ($rekap['persentase_kehadiran'] >= 50 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $rekap['persentase_kehadiran'] }}%"
                                             aria-valuenow="{{ $rekap['persentase_kehadiran'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="fw-bold text-{{ $rekap['persentase_kehadiran'] >= 75 ? 'success' : ($rekap['persentase_kehadiran'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ $rekap['persentase_kehadiran'] }}%
                                    </small>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($rekap['persentase_kehadiran'] >= 75)
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>Memenuhi
                                </span>
                                @else
                                <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>Kurang
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                <span class="text-muted">Tidak ada data mahasiswa</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Summary -->
            <div class="mt-3 pt-3 border-top">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Keterangan:</strong><br>
                            • Memenuhi: Persentase kehadiran ≥ 75%<br>
                            • Tidak Memenuhi: Persentase kehadiran < 75%
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            Total Mahasiswa: {{ count($rekapMahasiswa) }}<br>
                            @php
                            $memenuhi = collect($rekapMahasiswa)->filter(function($r) { return $r['persentase_kehadiran'] >= 75; })->count();
                            $tidakMemenuhi = collect($rekapMahasiswa)->filter(function($r) { return $r['persentase_kehadiran'] < 75; })->count();
                            @endphp
                            Memenuhi: {{ $memenuhi }} | Tidak Memenuhi: {{ $tidakMemenuhi }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.mahasiswa-row {
    transition: background-color 0.2s ease;
}

.mahasiswa-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
}

.sortable {
    user-select: none;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.badge.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.badge.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchMahasiswa');
    const tableRows = document.querySelectorAll('.mahasiswa-row');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            tableRows.forEach(row => {
                const nim = row.dataset.nim || '';
                const nama = row.dataset.nama || '';
                
                if (nim.includes(searchTerm) || nama.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Sorting functionality
    const sortableHeaders = document.querySelectorAll('.sortable');
    let currentSort = { column: null, direction: 'asc' };
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortType = this.dataset.sort;
            const tbody = document.querySelector('#tableMahasiswa tbody');
            const rows = Array.from(tbody.querySelectorAll('.mahasiswa-row'));
            
            // Toggle sort direction
            if (currentSort.column === sortType) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = sortType;
                currentSort.direction = 'asc';
            }
            
            // Update sort icons
            sortableHeaders.forEach(h => {
                const icon = h.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sort ms-1 text-muted';
                }
            });
            
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = currentSort.direction === 'asc' 
                    ? 'fas fa-sort-up ms-1 text-primary' 
                    : 'fas fa-sort-down ms-1 text-primary';
            }
            
            // Sort rows
            rows.sort((a, b) => {
                let aVal, bVal;
                
                switch(sortType) {
                    case 'nim':
                        aVal = a.dataset.nim;
                        bVal = b.dataset.nim;
                        break;
                    case 'nama':
                        aVal = a.dataset.nama;
                        bVal = b.dataset.nama;
                        break;
                    case 'hadir':
                        aVal = parseInt(a.dataset.hadir) || 0;
                        bVal = parseInt(b.dataset.hadir) || 0;
                        break;
                    case 'persentase':
                        aVal = parseFloat(a.dataset.persentase) || 0;
                        bVal = parseFloat(b.dataset.persentase) || 0;
                        break;
                    default:
                        return 0;
                }
                
                if (currentSort.direction === 'asc') {
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            // Re-append sorted rows
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
                tbody.appendChild(row);
            });
        });
    });
});
</script>
@endsection

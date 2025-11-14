@extends('layouts.master')

@section('title', 'Detail Rekap Absensi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="fas fa-chart-line me-2"></i>
                Detail Rekap Absensi
            </h4>
            <p class="text-muted mb-0">Rekap kehadiran per mahasiswa</p>
        </div>
        <div>
            <a href="{{ route('dosen.rekap-absensi.index') }}?semester_id={{ $semesterId }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $jadwal->id, 'format' => 'pdf']) }}?semester_id={{ $semesterId }}" 
               class="btn btn-success">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('dosen.rekap-absensi.export', ['jadwalId' => $jadwal->id, 'format' => 'excel']) }}?semester_id={{ $semesterId }}" 
               class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Download Excel
            </a>
        </div>
    </div>

    <!-- Informasi Kelas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Informasi Kelas
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Mata Kuliah</strong></td>
                            <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas</strong></td>
                            <td>{{ $jadwal->kelas->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Semester</strong></td>
                            <td>
                                @if(isset($jadwal->semester) && $jadwal->semester)
                                    {{ $jadwal->semester->tahun_ajaran }} - Semester {{ $jadwal->semester->semester }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Total Pertemuan</strong></td>
                            <td>{{ $pertemuanList->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Mahasiswa</strong></td>
                            <td>{{ $jadwal->mahasiswa->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Hari</strong></td>
                            <td>{{ ucfirst($jadwal->hari) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu</strong></td>
                            <td>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Keseluruhan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>
                Statistik Keseluruhan
            </h6>
        </div>
        <div class="card-body">
            @php
            $totalHadir = collect($rekapMahasiswa)->sum('hadir');
            $totalIzin = collect($rekapMahasiswa)->sum('izin');
            $totalSakit = collect($rekapMahasiswa)->sum('sakit');
            $totalAlpha = collect($rekapMahasiswa)->sum('alpha');
            $totalAll = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
            $avgPersentase = $totalAll > 0 ? round(($totalHadir / $totalAll) * 100, 2) : 0;
            @endphp
            
            <div class="row text-center">
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-success text-white rounded p-3">
                        <h4 class="mb-1">{{ $totalHadir }}</h4>
                        <small>Hadir</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-warning text-white rounded p-3">
                        <h4 class="mb-1">{{ $totalIzin }}</h4>
                        <small>Izin</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-danger text-white rounded p-3">
                        <h4 class="mb-1">{{ $totalSakit }}</h4>
                        <small>Sakit</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-secondary text-white rounded p-3">
                        <h4 class="mb-1">{{ $totalAlpha }}</h4>
                        <small>Alpha</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-primary text-white rounded p-3">
                        <h4 class="mb-1">{{ $totalAll }}</h4>
                        <small>Total</small>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="bg-info text-white rounded p-3">
                        <h4 class="mb-1">{{ $avgPersentase }}%</h4>
                        <small>Rata-rata</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Mahasiswa -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Daftar Kehadiran Mahasiswa
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th width="80">Hadir</th>
                            <th width="80">Izin</th>
                            <th width="80">Sakit</th>
                            <th width="80">Alpha</th>
                            <th width="80">Total</th>
                            <th width="100">Persentase</th>
                            <th width="80">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapMahasiswa as $index => $rekap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rekap['mahasiswa']->nim }}</td>
                            <td>{{ $rekap['mahasiswa']->nama }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $rekap['hadir'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $rekap['izin'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $rekap['sakit'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $rekap['alpha'] }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $rekap['total'] }}</strong>
                            </td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $rekap['persentase_kehadiran'] >= 75 ? 'success' : ($rekap['persentase_kehadiran'] >= 50 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $rekap['persentase_kehadiran'] }}%">
                                        {{ $rekap['persentase_kehadiran'] }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($rekap['persentase_kehadiran'] >= 75)
                                <span class="badge bg-success">Memenuhi</span>
                                @else
                                <span class="badge bg-danger">Tidak Memenuhi</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-3">
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
                            Total Mahasiswa: {{ $rekapMahasiswa->count() }}<br>
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
@endsection

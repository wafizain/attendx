@extends('layouts.master')

@section('title', 'Jadwal Mengajar')
@section('page-title', 'Jadwal Mengajar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Jadwal Mengajar
            </h1>
            <p class="text-muted mb-0">Kelola jadwal kuliah dan sesi absensi Anda</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dosen.jadwal-mengajar.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Hari</label>
                        <select name="hari" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Hari</option>
                            <option value="1" {{ request('hari') == '1' ? 'selected' : '' }}>Senin</option>
                            <option value="2" {{ request('hari') == '2' ? 'selected' : '' }}>Selasa</option>
                            <option value="3" {{ request('hari') == '3' ? 'selected' : '' }}>Rabu</option>
                            <option value="4" {{ request('hari') == '4' ? 'selected' : '' }}>Kamis</option>
                            <option value="5" {{ request('hari') == '5' ? 'selected' : '' }}>Jumat</option>
                            <option value="6" {{ request('hari') == '6' ? 'selected' : '' }}>Sabtu</option>
                            <option value="7" {{ request('hari') == '7' ? 'selected' : '' }}>Minggu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset Filter
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Jadwal Hari Ini -->
    @if($jadwalHariIni->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fas fa-calendar-day me-2"></i>
                <div>
                    <strong>📌 Jadwal Hari Ini ({{ ucfirst($todayIndo) }})</strong>
                    <div class="small">Berikut adalah jadwal mengajar Anda untuk hari ini</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        @forelse($jadwalHariIni as $jadwal)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-body">
                    <!-- Status Pertemuan (jika sedang berjalan) -->
                    @if($jadwal->pertemuan_hari_ini && $jadwal->pertemuan_hari_ini->status_sesi == 'berjalan')
                    <div class="alert alert-success alert-sm mb-3 text-center">
                        <i class="fas fa-circle text-success me-1"></i>
                        <strong>Pertemuan Sedang Berjalan</strong>
                    </div>
                    @endif
                    
                    <!-- Informasi Mata Kuliah -->
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-book me-2"></i>
                        {{ $jadwal->mataKuliah->nama_mk }}
                    </h6>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Kelas:</small>
                                <strong>{{ $jadwal->kelas ? $jadwal->kelas->nama : '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Ruangan:</small>
                                <strong>{{ $jadwal->ruangan->nama }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Hari:</small>
                                @php
                                    $hariMap = [
                                        1 => 'Senin',
                                        2 => 'Selasa',
                                        3 => 'Rabu',
                                        4 => 'Kamis',
                                        5 => 'Jumat',
                                        6 => 'Sabtu',
                                        7 => 'Minggu',
                                    ];
                                    $namaHari = $hariMap[$jadwal->hari] ?? ucfirst($jadwal->hari);
                                @endphp
                                <strong>{{ $namaHari }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Jam:</small>
                                <strong>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Mahasiswa:</small>
                                <span class="text-dark fw-bold">{{ $jadwal->mahasiswa->count() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Tambahan -->
                    @if($jadwal->pertemuan_hari_ini)
                    <div class="alert alert-info mb-3 py-2">
                        <small class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            @if($jadwal->pertemuan_hari_ini->status_sesi == 'direncanakan')
                                Pertemuan direncanakan
                            @elseif($jadwal->pertemuan_hari_ini->status_sesi == 'berjalan')
                                Pertemuan sedang berjalan
                            @else
                                Pertemuan selesai
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
                
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        @if($jadwal->pertemuan_hari_ini && $jadwal->pertemuan_hari_ini->status_sesi == 'direncanakan')
                        <button type="button" class="btn btn-success btn-sm flex-fill" 
                                data-bs-toggle="modal" data-bs-target="#mulaiKelasModal{{ $jadwal->id }}">
                            <i class="fas fa-play"></i> Mulai Pertemuan
                        </button>
                        @elseif($jadwal->pertemuan_hari_ini && $jadwal->pertemuan_hari_ini->status_sesi == 'berjalan')
                        <a href="{{ route('dosen.jadwal-mengajar.sesi', $jadwal->pertemuan_hari_ini->id) }}" 
                           class="btn btn-warning btn-sm flex-fill">
                            <i class="fas fa-users"></i> Kelola Absensi
                        </a>
                        @else
                        <form action="{{ route('dosen.jadwal-mengajar.mulai-kelas', $jadwal->id) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-play"></i> Mulai Pertemuan
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('dosen.jadwal-mengajar.show', $jadwal->id) }}" 
                           class="btn btn-info btn-sm flex-fill">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Mulai Kelas -->
        @if($jadwal->pertemuan_hari_ini && $jadwal->pertemuan_hari_ini->status_sesi == 'direncanakan')
        <div class="modal fade" id="mulaiKelasModal{{ $jadwal->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mulai Kelas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('dosen.jadwal-mengajar.mulai-kelas', $jadwal->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informasi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Sesi absensi akan dibuka otomatis</li>
                                    <li>Absensi sidik jari akan aktif 10 menit sebelum jam mulai</li>
                                    <li>Anda dapat melakukan absen manual untuk mahasiswa</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Topik Perkuliahan (Opsional)</label>
                                <input type="text" name="topik" class="form-control" 
                                       placeholder="Contoh: Pengenalan Laravel">
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <strong>Mata Kuliah:</strong><br>
                                    {{ $jadwal->mataKuliah->nama_mk }}
                                </div>
                                <div class="col-6">
                                    <strong>Waktu:</strong><br>
                                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play"></i> Mulai Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal Hari Ini</h5>
                    <p class="text-muted">Anda tidak memiliki jadwal mengajar untuk hari ini.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    @endif

    <!-- Jadwal Lainnya -->
    @if($jadwalLainnya->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-muted">
                <i class="fas fa-calendar-alt"></i>
                Jadwal Lainnya
            </h5>
        </div>
    </div>
    
    <div class="row">
        @forelse($jadwalLainnya as $jadwal)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <!-- Informasi Mata Kuliah -->
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-book me-2"></i>
                        {{ $jadwal->mataKuliah->nama_mk }}
                    </h6>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Kelas:</small>
                                <strong>{{ $jadwal->kelas ? $jadwal->kelas->nama : '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Ruangan:</small>
                                <strong>{{ $jadwal->ruangan->nama }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Hari:</small>
                                @php
                                    $hariMap = [
                                        1 => 'Senin',
                                        2 => 'Selasa',
                                        3 => 'Rabu',
                                        4 => 'Kamis',
                                        5 => 'Jumat',
                                        6 => 'Sabtu',
                                        7 => 'Minggu',
                                    ];
                                    $namaHari = $hariMap[$jadwal->hari] ?? ucfirst($jadwal->hari);
                                @endphp
                                <strong>{{ $namaHari }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Jam:</small>
                                <strong>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2" style="width: 60px;">Mahasiswa:</small>
                                <span class="text-dark fw-bold">{{ $jadwal->mahasiswa->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white">
                    <div class="d-grid gap-2 d-md-flex">
                        <form action="{{ route('dosen.jadwal-mengajar.mulai-kelas', $jadwal->id) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-play"></i> Mulai Pertemuan
                            </button>
                        </form>
                        <a href="{{ route('dosen.jadwal-mengajar.show', $jadwal->id) }}" 
                           class="btn btn-info btn-sm flex-fill">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal Lainnya</h5>
                    <p class="text-muted">Anda tidak memiliki jadwal mengajar lainnya.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    @endif

    <!-- Jika tidak ada jadwal sama sekali -->
    @if($jadwalHariIni->count() == 0 && $jadwalLainnya->count() == 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Jadwal Mengajar</h5>
                    <p class="text-muted">Anda belum memiliki jadwal mengajar yang aktif.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Berhasil</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert">
        <div class="toast-header bg-danger text-white">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong class="me-auto">Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{ session('error') }}
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto hide toasts after 5 seconds
setTimeout(function() {
    $('.toast').toast('hide');
}, 5000);
</script>
@endpush

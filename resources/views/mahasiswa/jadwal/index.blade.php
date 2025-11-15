@extends('layouts.master')

@section('title', 'Jadwal Kuliah')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="fas fa-calendar-alt text-primary me-2"></i>
                Jadwal Kuliah Saya
            </h2>
            <p class="text-muted">Lihat jadwal kuliah Anda minggu ini</p>
        </div>
    </div>

    @if($jadwalHariIni->count() > 0)
    <!-- Jadwal Hari Ini -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-clock text-success me-2"></i>
                Jadwal Hari Ini ({{ $hariMapping[$todayHariNumber] }})
            </h5>
        </div>
        @foreach($jadwalHariIni as $jadwal)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title fw-bold mb-0">{{ $jadwal->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h6>
                        <span class="badge bg-success">Hari Ini</span>
                    </div>
                    
                    <p class="text-muted small mb-3">{{ $jadwal->mataKuliah->kode_mk ?? '-' }} ({{ $jadwal->mataKuliah->sks ?? 0 }} SKS)</p>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-clock me-2"></i>
                            <strong>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</strong>
                        </small>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-door-open me-2"></i>
                            {{ $jadwal->ruangan->nama ?? 'Ruangan belum ditentukan' }}
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-user me-2"></i>
                            {{ $jadwal->dosen->name ?? 'Dosen' }}
                        </small>
                    </div>
                    
                    <a href="{{ route('mahasiswa.jadwal.show', $jadwal->id) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-eye me-1"></i>
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Jadwal Lainnya (Grouped by Hari) -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-calendar-week text-primary me-2"></i>
                Jadwal Minggu Ini
            </h5>
        </div>
        
        @forelse($jadwalByHari as $hari => $jadwalPerHari)
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-day me-2"></i>
                        {{ $hariMapping[$hari] }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($jadwalPerHari as $jadwal)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold">{{ $jadwal->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h6>
                                    <p class="text-muted small mb-3">{{ $jadwal->mataKuliah->kode_mk ?? '-' }} ({{ $jadwal->mataKuliah->sks ?? 0 }} SKS)</p>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-2"></i>
                                            <strong>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</strong>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-door-open me-2"></i>
                                            {{ $jadwal->ruangan->nama ?? 'Ruangan belum ditentukan' }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-2"></i>
                                            {{ $jadwal->dosen->name ?? 'Dosen' }}
                                        </small>
                                    </div>
                                    
                                    <a href="{{ route('mahasiswa.jadwal.show', $jadwal->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-eye me-1"></i>
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Ada Jadwal</h5>
                    <p class="text-muted">Anda belum memiliki jadwal kuliah. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Kelas Saya</h2>
            <p class="text-muted">Daftar kelas yang Anda ikuti</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse($kelasList as $kelas)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">{{ $kelas->nama_kelas }}</h5>
                        <span class="badge bg-success">Aktif</span>
                    </div>
                    
                    <h6 class="text-primary mb-3">{{ $kelas->mataKuliah->nama_matkul }}</h6>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-book me-2"></i>
                            Kode: {{ $kelas->mataKuliah->kode_matkul }}
                        </small>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-user me-2"></i>
                            Dosen: {{ $kelas->dosen->name }}
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-credit-card me-2"></i>
                            SKS: {{ $kelas->mataKuliah->sks }}
                        </small>
                    </div>
                    
                    @if($kelas->deskripsi)
                    <p class="card-text text-muted small">{{ Str::limit($kelas->deskripsi, 100) }}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-eye me-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Terdaftar di Kelas Manapun</h5>
                    <p class="text-muted">Anda belum terdaftar di kelas manapun. Silakan hubungi admin untuk mendaftarkan Anda ke kelas.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

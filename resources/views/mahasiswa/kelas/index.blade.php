@extends('layouts.master')

@section('title', 'Daftar Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-chalkboard text-primary me-2"></i>
                    Daftar Kelas Saya
                </h4>
            </div>

            @if($kelasList->count() > 0)
                <div class="row">
                    @foreach($kelasList as $kelas)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="fas fa-chalkboard text-primary fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1 fw-bold">{{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h6>
                                            <p class="text-muted small mb-0">{{ $kelas->mataKuliah->kode_mk ?? 'Kode MK' }}</p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-door-open me-1"></i>
                                            {{ $kelas->nama_kelas ?? $kelas->nama ?? 'Kelas' }}
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info ms-2">
                                            {{ $kelas->semester }} {{ $kelas->tahun_ajaran }}
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <small class="text-muted">Dosen Pengampu:</small><br>
                                            <strong>{{ $kelas->dosen->name ?? '-' }}</strong>
                                        </p>
                                        @if($kelas->ruangan)
                                            <p class="mb-0">
                                                <small class="text-muted">Ruangan:</small><br>
                                                <strong>{{ $kelas->ruangan }}</strong>
                                            </p>
                                        @endif
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>
                                            {{ $kelas->jumlah_mahasiswa_aktif ?? 0 }} mahasiswa
                                        </small>
                                        @if($kelas->status)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Kembali ke Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-chalkboard text-muted fs-1"></i>
                    </div>
                    <h5 class="text-muted">Belum Ada Kelas</h5>
                    <p class="text-muted">Anda belum terdaftar di kelas manapun.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <!-- Header dengan Greeting -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }}! 👋</h2>
            <p class="text-muted mb-0">Semangat belajar hari ini!</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end">
                <a href="{{ route('mahasiswa.jadwal') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-calendar-alt me-1"></i> Lihat Jadwal
                </a>
                <a href="{{ route('mahasiswa.absensi') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-history me-1"></i> Riwayat
                </a>
            </div>
        </div>
    </div>

    <!-- Jadwal Hari Ini -->
    @php
        $today = \Carbon\Carbon::today();
        $todayHariNumber = $today->dayOfWeek == 0 ? 7 : $today->dayOfWeek;
        $jadwalHariIni = \App\Models\JadwalKuliah::with(['mataKuliah', 'dosen', 'ruangan'])
            ->whereHas('mahasiswa', function($q) {
                $q->where('mahasiswa.id_user', auth()->id());
            })
            ->where('status', 'aktif')
            ->where('hari', $todayHariNumber)
            ->orderBy('jam_mulai', 'asc')
            ->get();
    @endphp

    @if($jadwalHariIni->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        Jadwal Hari Ini ({{ ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$todayHariNumber] }})
                    </h5>
                    <div class="row g-3">
                        @foreach($jadwalHariIni as $jadwal)
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">{{ $jadwal->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h6>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $jadwal->mataKuliah->sks ?? 0 }} SKS</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 mt-3">
                                        <div>
                                            <i class="fas fa-clock me-1"></i>
                                            <small>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</small>
                                        </div>
                                        <div>
                                            <i class="fas fa-door-open me-1"></i>
                                            <small>{{ $jadwal->ruangan->nama ?? '-' }}</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <i class="fas fa-user me-1"></i>
                                        <small>{{ $jadwal->dosen->name ?? '-' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    
</div>
@endsection

@extends('layouts.master')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Kelas: {{ $kelas->nama }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('kelas.members', $kelas->id) }}" class="btn btn-success" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                <i class="fas fa-users me-2"></i>Kelola Anggota
            </a>
            <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('kelas.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Info Kelas -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Kelas</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Kode</th>
                            <td><strong class="text-primary">{{ $kelas->kode }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $kelas->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>
                                @if($kelas->prodi)
                                    {{ $kelas->prodi->nama }} ({{ $kelas->prodi->jenjang }})
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Angkatan</th>
                            <td><span class="text-muted">{{ $kelas->angkatan }}</span></td>
                        </tr>
                        
                        
                        
                        <tr>
                            <th>Kapasitas</th>
                            <td>
                                @if($kelas->kapasitas)
                                    {{ $kelas->kapasitas }} mahasiswa
                                    @if($statistik['sisa_slot'] !== null)
                                        <br><small class="text-muted">Sisa slot: {{ $statistik['sisa_slot'] }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Tidak dibatasi</span>
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="text-{{ $kelas->status_badge === 'success' ? 'success' : ($kelas->status_badge === 'danger' ? 'danger' : 'secondary') }}">{{ $kelas->status_label }}</span>
                            </td>
                        </tr>
                        @if($kelas->catatan)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $kelas->catatan }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistik</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-graduate text-muted"></i> Mahasiswa Aktif</span>
                            <strong class="text-muted">{{ $statistik['total_mahasiswa_aktif'] }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-times text-muted"></i> Mahasiswa Keluar</span>
                            <strong class="text-muted">{{ $statistik['total_mahasiswa_keluar'] }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users text-muted"></i> Total Mahasiswa</span>
                            <strong class="text-muted">{{ $statistik['total_mahasiswa'] }}</strong>
                        </div>
                        <small class="text-muted">Termasuk histori</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt text-muted"></i> Jadwal</span>
                            <strong class="text-muted">{{ $statistik['total_jadwal'] }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clipboard-check text-muted"></i> Sesi Absensi</span>
                            <strong class="text-muted">{{ $statistik['total_sesi'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if($kelas->kapasitas)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kapasitas</h5>
                </div>
                <div class="card-body">
                    <div class="progress" style="height: 25px;">
                        @php
                            $percentage = ($statistik['total_mahasiswa_aktif'] / $kelas->kapasitas) * 100;
                        @endphp
                        <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                             role="progressbar" 
                             style="width: {{ min($percentage, 100) }}%">
                            {{ round($percentage, 1) }}%
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <small class="text-muted">
                            {{ $statistik['total_mahasiswa_aktif'] }} / {{ $kelas->kapasitas }} mahasiswa
                        </small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Anggota Aktif (Preview) -->
    @if($kelas->membersAktif->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Anggota Aktif ({{ $kelas->membersAktif->count() }})</h5>
            <a href="{{ route('kelas.members', $kelas->id) }}" class="btn btn-sm btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                Lihat Semua
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelas->membersAktif->take(10) as $member)
                        <tr>
                            <td>{{ $member->nim }}</td>
                            <td>{{ $member->mahasiswa->name ?? '-' }}</td>
                            <td>{{ $member->mahasiswa->email ?? '-' }}</td>
                            <td>{{ $member->tanggal_masuk->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($kelas->membersAktif->count() > 10)
                <div class="text-center">
                    <small class="text-muted">Menampilkan 10 dari {{ $kelas->membersAktif->count() }} anggota aktif</small>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Jadwal Kuliah (Sinkron dengan sistem baru) -->
    @if($kelas->jadwalKuliah->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Jadwal Kuliah ({{ $kelas->jadwalKuliah->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                            <th>Peserta</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kelas->jadwalKuliah->sortBy(['hari', 'jam_mulai']) as $jadwal)
                        <tr>
                            <td>
                                <strong>{{ $jadwal->mataKuliah->kode_mk }}</strong><br>
                                <small>{{ $jadwal->mataKuliah->nama_mk }}</small>
                            </td>
                            <td><small>{{ $jadwal->dosen->name }}</small></td>
                            <td><small>{{ $jadwal->hari_nama }}</small></td>
                            <td>
                                <small>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</small>
                            </td>
                            <td><small>{{ $jadwal->ruangan->kode }}</small></td>
                            <td class="text-center"><small>{{ $jadwal->mahasiswa->count() }}</small></td>
                            <td>
                                <a href="{{ route('jadwal.show', $jadwal->id) }}" class="btn btn-info btn-sm" title="Detail Jadwal" style="background-color:#4AA3FF;border-color:#4AA3FF;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

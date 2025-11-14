@extends('layouts.master')

@section('title','Detail Kelas')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .info-box { border-radius: 10px; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Detail Kelas: {{ $kelas->nama_kelas }}</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kelas.index') }}">Kelas</a></li>
        <li class="breadcrumb-item active">Detail</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <!-- Info Boxes -->
    <div class="row">
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-users"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Mahasiswa</span>
            <span class="info-box-number">{{ $kelas->jumlah_mahasiswa }} / {{ $kelas->kapasitas }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Jadwal</span>
            <span class="info-box-number">{{ $kelas->jadwal->count() }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Sesi Absensi</span>
            <span class="info-box-number">{{ $kelas->sesiAbsensi->count() }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-primary">
          <span class="info-box-icon"><i class="fas fa-book"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">SKS</span>
            <span class="info-box-number">{{ $kelas->mataKuliah->sks }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Info -->
    <div class="row">
      <div class="col-md-6">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Informasi Kelas</h3>
            <div class="card-tools">
              <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn btn-tool">
                <i class="fas fa-edit"></i>
              </a>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <th width="40%">Nama Kelas</th>
                <td><strong>{{ $kelas->nama_kelas }}</strong></td>
              </tr>
              <tr>
                <th>Mata Kuliah</th>
                <td>
                  <span class="text-monospace text-primary">{{ $kelas->mataKuliah->kode_mk }}</span><br>
                  {{ $kelas->mataKuliah->nama_mk }}
                </td>
              </tr>
              <tr>
                <th>Dosen Pengampu</th>
                <td>{{ $kelas->dosen->name }}</td>
              </tr>
              <tr>
                <th>Tahun Ajaran</th>
                <td>{{ $kelas->tahun_ajaran }}</td>
              </tr>
              <tr>
                <th>Semester</th>
                <td><span class="badge badge-info">{{ ucfirst($kelas->semester) }}</span></td>
              </tr>
              <tr>
                <th>Ruangan</th>
                <td>{{ $kelas->ruangan ?? '-' }}</td>
              </tr>
              <tr>
                <th>Kapasitas</th>
                <td>{{ $kelas->kapasitas }} mahasiswa</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>
                  @if($kelas->status == 'aktif')
                    <span class="badge badge-success">Aktif</span>
                  @else
                    <span class="badge badge-secondary">Nonaktif</span>
                  @endif
                </td>
              </tr>
            </table>
          </div>
          <div class="card-footer">
            <a href="{{ route('kelas.mahasiswa', $kelas->id) }}" class="btn btn-success btn-sm">
              <i class="fas fa-users mr-1"></i> Kelola Mahasiswa
            </a>
            <a href="{{ route('kelas.jadwal', $kelas->id) }}" class="btn btn-warning btn-sm">
              <i class="fas fa-calendar mr-1"></i> Kelola Jadwal
            </a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <!-- Jadwal Kelas -->
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Jadwal Kelas</h3>
          </div>
          <div class="card-body">
            @if($kelas->jadwal->count() > 0)
              <ul class="list-unstyled">
                @foreach($kelas->jadwal as $jadwal)
                  <li class="mb-2">
                    <i class="fas fa-calendar-day text-primary"></i>
                    <strong>{{ $jadwal->hari }}</strong><br>
                    <small class="ml-3">
                      {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                      {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                      @if($jadwal->ruangan)
                        | {{ $jadwal->ruangan }}
                      @endif
                    </small>
                  </li>
                @endforeach
              </ul>
            @else
              <p class="text-muted">Belum ada jadwal</p>
            @endif
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Quick Actions</h3>
          </div>
          <div class="card-body">
            <a href="{{ route('sesi-absensi.create') }}?kelas_id={{ $kelas->id }}" class="btn btn-primary btn-block">
              <i class="fas fa-plus mr-1"></i> Buat Sesi Absensi
            </a>
            <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-block">
              <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

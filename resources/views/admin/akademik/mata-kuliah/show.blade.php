@extends('layouts.master')

@section('title','Detail Mata Kuliah')

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
      <h1 class="h4 mb-0">Detail Mata Kuliah</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mata-kuliah.index') }}">Mata Kuliah</a></li>
        <li class="breadcrumb-item active">Detail</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <!-- Info Boxes -->
    <div class="row">
      <div class="col-md-4">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-book"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">SKS</span>
            <span class="info-box-number">{{ $mataKuliah->sks }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-chalkboard"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Jumlah Kelas</span>
            <span class="info-box-number">{{ $mataKuliah->kelas->count() }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-users"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Mahasiswa</span>
            <span class="info-box-number">{{ $mataKuliah->kelas->sum(function($k) { return $k->mahasiswa->count(); }) }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Info Mata Kuliah -->
      <div class="col-md-6">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Informasi Mata Kuliah</h3>
            <div class="card-tools">
              <a href="{{ route('mata-kuliah.edit', $mataKuliah->id) }}" class="btn btn-tool">
                <i class="fas fa-edit"></i>
              </a>
            </div>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <th width="40%">Kode MK</th>
                <td><span class="text-monospace text-primary"><strong>{{ $mataKuliah->kode_mk }}</strong></span></td>
              </tr>
              <tr>
                <th>Nama Mata Kuliah</th>
                <td><strong>{{ $mataKuliah->nama_mk }}</strong></td>
              </tr>
              <tr>
                <th>SKS</th>
                <td><span class="badge badge-info">{{ $mataKuliah->sks }} SKS</span></td>
              </tr>
              <tr>
                <th>Semester</th>
                <td>{{ $mataKuliah->semester ? 'Semester ' . $mataKuliah->semester : '-' }}</td>
              </tr>
              <tr>
                <th>Status</th>
                <td>
                  @if($mataKuliah->status == 'aktif')
                    <span class="badge badge-success">Aktif</span>
                  @else
                    <span class="badge badge-secondary">Nonaktif</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Deskripsi</th>
                <td>{{ $mataKuliah->deskripsi ?? '-' }}</td>
              </tr>
              <tr>
                <th>Dibuat</th>
                <td><small>{{ $mataKuliah->created_at->format('d/m/Y H:i') }}</small></td>
              </tr>
            </table>
          </div>
          <div class="card-footer">
            <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
          </div>
        </div>
      </div>

      <!-- Daftar Kelas -->
      <div class="col-md-6">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Kelas yang Menggunakan MK Ini</h3>
          </div>
          <div class="card-body">
            @if($mataKuliah->kelas->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>Kelas</th>
                      <th>Dosen</th>
                      <th>TA</th>
                      <th>Mahasiswa</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($mataKuliah->kelas as $kelas)
                      <tr>
                        <td><strong>{{ $kelas->nama_kelas }}</strong></td>
                        <td><small>{{ $kelas->dosen->name }}</small></td>
                        <td><small>{{ $kelas->tahun_ajaran }}</small></td>
                        <td><span class="badge badge-info">{{ $kelas->mahasiswa->count() }}</span></td>
                        <td>
                          <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p class="text-center text-muted py-4">Belum ada kelas yang menggunakan mata kuliah ini</p>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

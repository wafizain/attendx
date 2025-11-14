@extends('layouts.master')

@section('title','Laporan Mahasiswa')

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
      <h1 class="h4 mb-0">Laporan Absensi Mahasiswa</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Per Mahasiswa</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <!-- Student Info -->
    <div class="card card-modern">
      <div class="card-header bg-success">
        <h3 class="card-title mb-0 text-white">
          <i class="fas fa-user mr-2"></i> Informasi Mahasiswa
        </h3>
        <div class="card-tools">
          <form action="{{ route('reports.export-student-csv') }}" method="GET" class="d-inline">
            <input type="hidden" name="mahasiswa_id" value="{{ $mahasiswa->id }}">
            <input type="hidden" name="start_date" value="{{ $request->start_date }}">
            <input type="hidden" name="end_date" value="{{ $request->end_date }}">
            <button type="submit" class="btn btn-warning btn-sm">
              <i class="fas fa-file-csv mr-1"></i> Export CSV
            </button>
          </form>
          <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm ml-2">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="30%">Nama</th>
                <td><strong>{{ $mahasiswa->name }}</strong></td>
              </tr>
              <tr>
                <th>NIM</th>
                <td>{{ $mahasiswa->no_induk ?? '-' }}</td>
              </tr>
              <tr>
                <th>Email</th>
                <td>{{ $mahasiswa->email }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="30%">Periode</th>
                <td>
                  {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }}
                  s/d
                  {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Sekarang' }}
                </td>
              </tr>
              <tr>
                <th>Total Absensi</th>
                <td><span class="badge badge-info">{{ $absensiList->count() }} Record</span></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary by Class -->
    <div class="row">
      @foreach($reportByKelas as $data)
        <div class="col-md-4">
          <div class="card card-modern">
            <div class="card-header">
              <h3 class="card-title mb-0">
                <strong>{{ $data['kelas']->mataKuliah->nama_mk }}</strong>
              </h3>
            </div>
            <div class="card-body">
              <p class="mb-2"><small class="text-muted">Kelas: {{ $data['kelas']->nama_kelas }}</small></p>
              <div class="row text-center">
                <div class="col-3">
                  <div class="text-success"><strong>{{ $data['hadir'] }}</strong></div>
                  <small>Hadir</small>
                </div>
                <div class="col-3">
                  <div class="text-warning"><strong>{{ $data['izin'] }}</strong></div>
                  <small>Izin</small>
                </div>
                <div class="col-3">
                  <div class="text-info"><strong>{{ $data['sakit'] }}</strong></div>
                  <small>Sakit</small>
                </div>
                <div class="col-3">
                  <div class="text-danger"><strong>{{ $data['alpha'] }}</strong></div>
                  <small>Alpha</small>
                </div>
              </div>
              <hr>
              <div class="text-center">
                <h4 class="mb-0 {{ $data['persentase'] >= 75 ? 'text-success' : 'text-danger' }}">
                  {{ $data['persentase'] }}%
                </h4>
                <small class="text-muted">Persentase Kehadiran</small>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Detailed Attendance List -->
    <div class="card card-modern">
      <div class="card-header">
        <h3 class="card-title mb-0">Detail Absensi</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Mata Kuliah</th>
                <th>Kelas</th>
                <th>Topik</th>
                <th>Status</th>
                <th>Waktu Absen</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($absensiList as $index => $absensi)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $absensi->sesiAbsensi->tanggal->format('d/m/Y') }}</td>
                  <td>{{ $absensi->sesiAbsensi->kelas->mataKuliah->nama_mk }}</td>
                  <td>{{ $absensi->sesiAbsensi->kelas->nama_kelas }}</td>
                  <td>{{ $absensi->sesiAbsensi->topik }}</td>
                  <td>
                    @if($absensi->status == 'hadir')
                      <span class="badge badge-success">Hadir</span>
                    @elseif($absensi->status == 'izin')
                      <span class="badge badge-warning">Izin</span>
                    @elseif($absensi->status == 'sakit')
                      <span class="badge badge-info">Sakit</span>
                    @else
                      <span class="badge badge-danger">Alpha</span>
                    @endif
                  </td>
                  <td>
                    @if($absensi->waktu_absen)
                      {{ $absensi->waktu_absen->format('H:i:s') }}
                      @if($absensi->isLate())
                        <span class="badge badge-warning">Terlambat</span>
                      @endif
                    @else
                      -
                    @endif
                  </td>
                  <td><small>{{ $absensi->keterangan ?? '-' }}</small></td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    Tidak ada data absensi
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@extends('layouts.master')

@section('title','Laporan Kelas')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .table-attendance { font-size: 12px; }
  .table-attendance th { background: #f8f9fa; position: sticky; top: 0; z-index: 10; }
  .status-badge { padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
  .status-H { background: #28a745; color: white; }
  .status-I { background: #ffc107; color: #000; }
  .status-S { background: #17a2b8; color: white; }
  .status-A { background: #dc3545; color: white; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Laporan Absensi Kelas</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li>
        <li class="breadcrumb-item active">Per Kelas</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <!-- Class Info -->
    <div class="card card-modern">
      <div class="card-header bg-primary">
        <h3 class="card-title mb-0 text-white">
          <i class="fas fa-info-circle mr-2"></i> Informasi Kelas
        </h3>
        <div class="card-tools">
          <form action="{{ route('reports.export-class-csv') }}" method="GET" class="d-inline">
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="start_date" value="{{ $request->start_date }}">
            <input type="hidden" name="end_date" value="{{ $request->end_date }}">
            <button type="submit" class="btn btn-success btn-sm">
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
                <th width="40%">Mata Kuliah</th>
                <td><strong>{{ $kelas->mataKuliah->nama_mk }}</strong></td>
              </tr>
              <tr>
                <th>Kelas</th>
                <td>{{ $kelas->nama_kelas }}</td>
              </tr>
              <tr>
                <th>Dosen</th>
                <td>{{ $kelas->dosen->name }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="40%">Tahun Ajaran</th>
                <td>{{ $kelas->tahun_ajaran }}</td>
              </tr>
              <tr>
                <th>Periode</th>
                <td>
                  {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }}
                  s/d
                  {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Sekarang' }}
                </td>
              </tr>
              <tr>
                <th>Total Sesi</th>
                <td><span class="badge badge-info">{{ $sesiList->count() }} Sesi</span></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Table -->
    <div class="card card-modern">
      <div class="card-header">
        <h3 class="card-title mb-0">Rekap Kehadiran</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
          <table class="table table-bordered table-hover table-attendance mb-0">
            <thead>
              <tr>
                <th rowspan="2" style="vertical-align: middle;">No</th>
                <th rowspan="2" style="vertical-align: middle;">NIM</th>
                <th rowspan="2" style="vertical-align: middle;">Nama Mahasiswa</th>
                <th colspan="{{ $sesiList->count() }}" class="text-center">Tanggal Pertemuan</th>
                <th rowspan="2" style="vertical-align: middle;">H</th>
                <th rowspan="2" style="vertical-align: middle;">I</th>
                <th rowspan="2" style="vertical-align: middle;">S</th>
                <th rowspan="2" style="vertical-align: middle;">A</th>
                <th rowspan="2" style="vertical-align: middle;">%</th>
              </tr>
              <tr>
                @foreach($sesiList as $sesi)
                  <th class="text-center" style="min-width: 60px;">
                    <small>{{ $sesi->tanggal->format('d/m') }}</small>
                  </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @forelse($reportData as $index => $data)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><small>{{ $data['mahasiswa']->no_induk ?? '-' }}</small></td>
                  <td>{{ $data['mahasiswa']->name }}</td>
                  @foreach($data['detail'] as $detail)
                    <td class="text-center">
                      @php
                        $statusMap = ['hadir' => 'H', 'izin' => 'I', 'sakit' => 'S', 'alpha' => 'A'];
                        $statusChar = $statusMap[$detail['status']] ?? 'A';
                      @endphp
                      <span class="status-badge status-{{ $statusChar }}">{{ $statusChar }}</span>
                    </td>
                  @endforeach
                  <td class="text-center"><strong>{{ $data['hadir'] }}</strong></td>
                  <td class="text-center">{{ $data['izin'] }}</td>
                  <td class="text-center">{{ $data['sakit'] }}</td>
                  <td class="text-center">{{ $data['alpha'] }}</td>
                  <td class="text-center">
                    <strong class="{{ $data['persentase'] >= 75 ? 'text-success' : 'text-danger' }}">
                      {{ $data['persentase'] }}%
                    </strong>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ 8 + $sesiList->count() }}" class="text-center text-muted py-4">
                    Tidak ada data absensi
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-md-12">
            <strong>Keterangan:</strong>
            <span class="status-badge status-H ml-2">H</span> Hadir
            <span class="status-badge status-I ml-2">I</span> Izin
            <span class="status-badge status-S ml-2">S</span> Sakit
            <span class="status-badge status-A ml-2">A</span> Alpha
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

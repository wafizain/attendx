@extends('layouts.master')

@section('title', 'Halaman Pertemuan')
@section('page-title', 'Halaman Pertemuan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Halaman Pertemuan
            </h1>
            <p class="text-muted mb-0">
                {{ $sesiAbsensi->kelas->mataKuliah ? $sesiAbsensi->kelas->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }} - 
                {{ $sesiAbsensi->kelas->nama_kelas }}
            </p>
        </div>
        <div>
            @if($sesiAbsensi->status == 'aktif')
            <form action="{{ route('dosen.jadwal-mengajar.tutup-sesi', $sesiAbsensi->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Yakin ingin mengakhiri pertemuan?')">
                    <i class="fas fa-stop"></i> Akhiri Pertemuan
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- A. Informasi Pertemuan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pertemuan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Mata Kuliah:</strong></td>
                            <td>{{ $sesiAbsensi->kelas->mataKuliah ? $sesiAbsensi->kelas->mataKuliah->nama_mk : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas:</strong></td>
                            <td>{{ $sesiAbsensi->kelas->nama_kelas }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pertemuan ke:</strong></td>
                            <td>{{ $sesiAbsensi->pertemuan_ke ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Jam Mulai:</strong></td>
                            <td>{{ $sesiAbsensi->waktu_mulai ? $sesiAbsensi->waktu_mulai->format('H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Mahasiswa:</strong></td>
                            <td>{{ $sesiAbsensi->absensi->count() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status Pertemuan:</strong></td>
                            <td>
                                @if($sesiAbsensi->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- B. Statistik Kehadiran (Live) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistik Kehadiran (Live)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-fingerprint fa-2x text-success mb-2"></i>
                            <h5 class="text-success">{{ $stats['hadir_fingerprint'] ?? 0 }}</h5>
                            <small class="text-muted">Hadir (Otomatis)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-user-edit fa-2x text-info mb-2"></i>
                            <h5 class="text-info">{{ $stats['hadir_manual'] ?? 0 }}</h5>
                            <small class="text-muted">Hadir (Manual)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-2x text-warning mb-2"></i>
                            <h5 class="text-warning">{{ $stats['izin'] }}</h5>
                            <small class="text-muted">Izin</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="fas fa-notes-medical fa-2x text-danger mb-2"></i>
                            <h5 class="text-danger">{{ $stats['sakit'] }}</h5>
                            <small class="text-muted">Sakit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-question-circle fa-2x text-secondary mb-2"></i>
                            <h5 class="text-secondary">{{ $stats['alpha'] }}</h5>
                            <small class="text-muted">Belum Absen</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-2 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h5>{{ $sesiAbsensi->absensi->count() }}</h5>
                            <small>Total Mahasiswa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Daftar Mahasiswa + Status -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-users"></i> Daftar Mahasiswa + Status</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Jam</th>
                            <th>Metode</th>
                            <th>Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $hadirFingerprint = 0;
                        $hadirManual = 0;
                        @endphp
                        @foreach($sesiAbsensi->absensi as $index => $absensi)
                        @php
                        if($absensi->status == 'hadir' && $absensi->metode == 'fingerprint') $hadirFingerprint++;
                        if($absensi->status == 'hadir' && $absensi->metode == 'manual') $hadirManual++;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $absensi->mahasiswa->nama }}</td>
                            <td>
                                @if($absensi->status == 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($absensi->status == 'izin')
                                    <span class="badge bg-warning">Izin</span>
                                @elseif($absensi->status == 'sakit')
                                    <span class="badge bg-danger">Sakit</span>
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                            <td>
                                @if($absensi->waktu_absen)
                                    {{ $absensi->waktu_absen->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($absensi->metode)
                                    <span class="badge bg-{{ $absensi->metode == 'fingerprint' ? 'success' : 'info' }}">
                                        {{ ucfirst($absensi->metode) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($absensi->foto)
                                    <img src="{{ asset('storage/foto_absensi/' . $absensi->foto) }}" 
                                         alt="Foto" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- D. Absensi Manual -->
    @if($sesiAbsensi->status == 'aktif')
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-user-edit"></i> Absensi Manual</h6>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#absensiManualModal">
                <i class="fas fa-user-edit"></i> Absensi Manual Mahasiswa
            </button>
        </div>
    </div>
    @endif

    <!-- Modal Absensi Manual -->
    <div class="modal fade" id="absensiManualModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Absensi Manual Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dosen.jadwal-mengajar.absen-manual', $sesiAbsensi->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Nama</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sesiAbsensi->absensi as $absensi)
                                    @if($absensi->status == 'alpha' || $absensi->status == null)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="mahasiswa_id[]" value="{{ $absensi->mahasiswa_id }}" 
                                                   class="form-check-input mahasiswa-checkbox" data-nama="{{ $absensi->mahasiswa->nama }}">
                                        </td>
                                        <td>{{ $absensi->mahasiswa->nama }}</td>
                                        <td>
                                            <select name="status_{{ $absensi->mahasiswa_id }}" class="form-select form-select-sm" disabled>
                                                <option value="">Pilih Status</option>
                                                <option value="hadir">Hadir</option>
                                                <option value="izin">Izin</option>
                                                <option value="sakit">Sakit</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Absensi Manual</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAll').change(function() {
        $('.mahasiswa-checkbox').prop('checked', $(this).prop('checked'));
        toggleStatusSelects();
    });
    
    $('.mahasiswa-checkbox').change(function() {
        toggleStatusSelects();
        updateSelectAllCheckbox();
    });
    
    function toggleStatusSelects() {
        $('.mahasiswa-checkbox').each(function() {
            var checkbox = $(this);
            var statusSelect = $('[name="status_' + checkbox.val() + '"]');
            statusSelect.prop('disabled', !checkbox.prop('checked'));
        });
    }
    
    function updateSelectAllCheckbox() {
        var allCheckboxes = $('.mahasiswa-checkbox');
        var checkedCheckboxes = $('.mahasiswa-checkbox:checked');
        $('#selectAll').prop('checked', allCheckboxes.length === checkedCheckboxes.length);
    }
    
    // Auto refresh statistics every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});
</script>
@endpush

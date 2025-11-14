@extends('layouts.master')

@section('title','Detail Sesi Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .info-box { border-radius: 10px; }
  .qr-code-container { text-align: center; padding: 20px; background: #f9fafb; border-radius: 10px; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Detail Sesi Absensi</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sesi-absensi.index') }}">Sesi Absensi</a></li>
        <li class="breadcrumb-item active">Detail</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    <!-- Statistik -->
    <div class="row">
      @php $stats = $sesiAbsensi->statistik; @endphp
      <div class="col-md-3">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Hadir</span>
            <span class="info-box-number">{{ $stats['hadir'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Izin</span>
            <span class="info-box-number">{{ $stats['izin'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-notes-medical"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Sakit</span>
            <span class="info-box-number">{{ $stats['sakit'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-danger">
          <span class="info-box-icon"><i class="fas fa-times"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Alpha</span>
            <span class="info-box-number">{{ $stats['alpha'] }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Info Sesi -->
      <div class="col-md-8">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Informasi Sesi</h3>
            <div class="card-tools">
              @if($sesiAbsensi->status == 'draft' || $sesiAbsensi->status == 'aktif')
                <a href="{{ route('sesi-absensi.edit', $sesiAbsensi->id) }}" class="btn btn-tool">
                  <i class="fas fa-edit"></i>
                </a>
              @endif
            </div>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <th width="30%">Kelas</th>
                <td><strong>{{ $sesiAbsensi->kelas->nama_kelas }}</strong></td>
              </tr>
              <tr>
                <th>Mata Kuliah</th>
                <td>
                  <span class="text-monospace text-primary">{{ $sesiAbsensi->kelas->mataKuliah ? $sesiAbsensi->kelas->mataKuliah->kode_mk : '-' }}</span><br>
                  {{ $sesiAbsensi->kelas->mataKuliah ? $sesiAbsensi->kelas->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }}
                </td>
              </tr>
              <tr>
                <th>Dosen</th>
                <td>{{ $sesiAbsensi->kelas->dosen ? $sesiAbsensi->kelas->dosen->name : 'Dosen Tidak Ditemukan' }}</td>
              </tr>
              <tr>
                <th>Tanggal</th>
                <td><strong>{{ $sesiAbsensi->tanggal->format('d F Y') }}</strong></td>
              </tr>
              <tr>
                <th>Pertemuan Ke-</th>
                <td>{{ $sesiAbsensi->pertemuan_ke ?? '-' }}</td>
              </tr>
              <tr>
                <th>Topik</th>
                <td>{{ $sesiAbsensi->topik ?? '-' }}</td>
              </tr>
              <tr>
                <th>Waktu</th>
                <td>
                  <i class="far fa-clock"></i>
                  {{ $sesiAbsensi->waktu_mulai->format('H:i') }} - {{ $sesiAbsensi->waktu_selesai->format('H:i') }}
                  <br><small class="text-muted">Durasi: {{ $sesiAbsensi->waktu_mulai->diffInMinutes($sesiAbsensi->waktu_selesai) }} menit</small>
                </td>
              </tr>
              <tr>
                <th>Metode</th>
                <td>
                  @if($sesiAbsensi->metode == 'manual')
                    <span class="badge badge-secondary">Manual</span>
                  @elseif($sesiAbsensi->metode == 'qr_code')
                    <span class="badge badge-info">QR Code</span>
                  @else
                    <span class="badge badge-warning">Geolocation</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Status</th>
                <td>
                  @if($sesiAbsensi->status == 'draft')
                    <span class="badge badge-secondary">Draft</span>
                  @elseif($sesiAbsensi->status == 'aktif')
                    <span class="badge badge-success">Aktif</span>
                  @elseif($sesiAbsensi->status == 'selesai')
                    <span class="badge badge-primary">Selesai</span>
                  @else
                    <span class="badge badge-danger">Dibatalkan</span>
                  @endif
                </td>
              </tr>
              @if($sesiAbsensi->catatan)
                <tr>
                  <th>Catatan</th>
                  <td>{{ $sesiAbsensi->catatan }}</td>
                </tr>
              @endif
            </table>
          </div>
          @if($sesiAbsensi->status == 'aktif')
            <div class="card-footer">
              <form action="{{ route('sesi-absensi.close', $sesiAbsensi->id) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success" onclick="return confirm('Tutup sesi absensi ini?')">
                  <i class="fas fa-check mr-1"></i> Tutup Sesi
                </button>
              </form>
            </div>
          @endif
        </div>
      </div>

      <!-- QR Code / Info Tambahan -->
      <div class="col-md-4">
        @if($sesiAbsensi->metode == 'qr_code' && $sesiAbsensi->kode_absensi)
          <div class="card card-modern">
            <div class="card-header bg-info">
              <h3 class="card-title mb-0 text-white">QR Code Absensi</h3>
            </div>
            <div class="card-body">
              <div class="qr-code-container">
                <div id="qrcode"></div>
                <p class="mt-3 mb-0"><strong>{{ $sesiAbsensi->kode_absensi }}</strong></p>
              </div>
            </div>
          </div>
        @endif

        @if($sesiAbsensi->metode == 'geolocation')
          <div class="card card-modern">
            <div class="card-header bg-warning">
              <h3 class="card-title mb-0">Lokasi Absensi</h3>
            </div>
            <div class="card-body">
              <p><strong>Latitude:</strong> {{ $sesiAbsensi->latitude }}</p>
              <p><strong>Longitude:</strong> {{ $sesiAbsensi->longitude }}</p>
              <p><strong>Radius:</strong> {{ $sesiAbsensi->radius_meter }} meter</p>
              @if($sesiAbsensi->latitude && $sesiAbsensi->longitude)
                <a href="https://www.google.com/maps?q={{ $sesiAbsensi->latitude }},{{ $sesiAbsensi->longitude }}" 
                   target="_blank" class="btn btn-sm btn-info btn-block">
                  <i class="fas fa-map-marker-alt"></i> Lihat di Google Maps
                </a>
              @endif
            </div>
          </div>
        @endif

        <div class="card card-modern">
          <div class="card-body">
            <a href="{{ route('sesi-absensi.index') }}" class="btn btn-secondary btn-block">
              <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Daftar Absensi Mahasiswa -->
    <div class="card card-modern">
      <div class="card-header">
        <h3 class="card-title mb-0">Daftar Absensi Mahasiswa ({{ $stats['total'] }})</h3>
      </div>
      <div class="card-body">
        <table id="absensi-table" class="table table-hover table-sm">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Mahasiswa</th>
              <th>Email</th>
              <th>Status</th>
              <th>Waktu Absen</th>
              <th>Keterangan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sesiAbsensi->absensi as $index => $abs)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $abs->mahasiswa->name }}</strong></td>
                <td><small>{{ $abs->mahasiswa->email }}</small></td>
                <td>
                  @if($abs->status == 'hadir')
                    <span class="badge badge-success">Hadir</span>
                  @elseif($abs->status == 'izin')
                    <span class="badge badge-warning">Izin</span>
                  @elseif($abs->status == 'sakit')
                    <span class="badge badge-info">Sakit</span>
                  @else
                    <span class="badge badge-danger">Alpha</span>
                  @endif
                </td>
                <td>
                  @if($abs->waktu_absen)
                    {{ $abs->waktu_absen->format('H:i:s') }}
                    @if($abs->isTerlambat())
                      <br><small class="text-danger">Terlambat {{ $abs->durasi_terlambat }} menit</small>
                    @endif
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td><small>{{ $abs->keterangan ?? '-' }}</small></td>
                <td>
                  <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" 
                          data-target="#updateModal{{ $abs->id }}">
                    <i class="fas fa-edit"></i>
                  </button>

                  <!-- Update Modal -->
                  <div class="modal fade" id="updateModal{{ $abs->id }}" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Update Absensi: {{ $abs->mahasiswa->name }}</h5>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <form action="{{ route('sesi-absensi.update-absensi', [$sesiAbsensi->id, $abs->mahasiswa_id]) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <div class="modal-body">
                            <div class="form-group">
                              <label>Status <span class="text-danger">*</span></label>
                              <select name="status" class="form-control" required>
                                <option value="hadir" {{ $abs->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ $abs->status == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ $abs->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ $abs->status == 'alpha' ? 'selected' : '' }}>Alpha</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label>Keterangan</label>
                              <textarea name="keterangan" class="form-control" rows="2">{{ $abs->keterangan }}</textarea>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('AdminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
@if($sesiAbsensi->metode == 'qr_code' && $sesiAbsensi->kode_absensi)
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endif
<script>
  $(function () {
    $('#absensi-table').DataTable({
      responsive: true,
      autoWidth: false,
      pageLength: 50,
      language: {
        search: "Cari:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
        paginate: { next:"Berikutnya", previous:"Sebelumnya" }
      }
    });

    @if($sesiAbsensi->metode == 'qr_code' && $sesiAbsensi->kode_absensi)
      // Generate QR Code
      new QRCode(document.getElementById("qrcode"), {
        text: "{{ $sesiAbsensi->kode_absensi }}",
        width: 200,
        height: 200
      });
    @endif
  });
</script>
@endpush

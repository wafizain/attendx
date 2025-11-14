@extends('layouts.master')

@section('title','Buat Sesi Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; }
  #geolocation-fields { display: none; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Buat Sesi Absensi</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sesi-absensi.index') }}">Sesi Absensi</a></li>
        <li class="breadcrumb-item active">Buat</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Form Buat Sesi Absensi</h3>
          </div>

          <form action="{{ route('sesi-absensi.store') }}" method="POST">
            @csrf
            <div class="card-body">
              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <div class="form-group">
                <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" id="kelas_id" class="form-control select2" required>
                  <option value="">Pilih Kelas</option>
                  @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ old('kelas_id', request('kelas_id')) == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} - {{ $k->mataKuliah ? $k->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }} ({{ $k->dosen ? $k->dosen->name : 'Dosen Tidak Ditemukan' }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" 
                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="pertemuan_ke">Pertemuan Ke-</label>
                    <input type="number" name="pertemuan_ke" id="pertemuan_ke" class="form-control" 
                           placeholder="1" value="{{ old('pertemuan_ke') }}" min="1">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="topik">Topik Perkuliahan</label>
                <input type="text" name="topik" id="topik" class="form-control" 
                       placeholder="Contoh: Pengenalan Laravel" value="{{ old('topik') }}">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="waktu_mulai">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" class="form-control" 
                           value="{{ old('waktu_mulai') }}" required>
                    <small class="text-muted">Waktu mulai absensi dibuka</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="waktu_selesai">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" class="form-control" 
                           value="{{ old('waktu_selesai') }}" required>
                    <small class="text-muted">Waktu absensi ditutup</small>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Metode Absensi <span class="text-danger">*</span></label>
                <div class="row">
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_manual" 
                             value="manual" {{ old('metode', 'manual') == 'manual' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_manual">
                        <i class="fas fa-hand-paper"></i> Manual
                      </label>
                    </div>
                    <small class="text-muted">Dosen input manual</small>
                  </div>
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_qr" 
                             value="qr_code" {{ old('metode') == 'qr_code' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_qr">
                        <i class="fas fa-qrcode"></i> QR Code
                      </label>
                    </div>
                    <small class="text-muted">Scan QR Code</small>
                  </div>
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_geo" 
                             value="geolocation" {{ old('metode') == 'geolocation' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_geo">
                        <i class="fas fa-map-marker-alt"></i> Geolocation
                      </label>
                    </div>
                    <small class="text-muted">Validasi lokasi</small>
                  </div>
                </div>
              </div>

              <!-- Geolocation Fields -->
              <div id="geolocation-fields">
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i>
                  <strong>Geolocation:</strong> Mahasiswa harus berada dalam radius yang ditentukan untuk bisa absen.
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="latitude">Latitude</label>
                      <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control" 
                             placeholder="-6.200000" value="{{ old('latitude') }}">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="longitude">Longitude</label>
                      <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control" 
                             placeholder="106.816666" value="{{ old('longitude') }}">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="radius_meter">Radius (meter)</label>
                      <input type="number" name="radius_meter" id="radius_meter" class="form-control" 
                             placeholder="100" value="{{ old('radius_meter', 100) }}" min="1">
                    </div>
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-info" onclick="getLocation()">
                  <i class="fas fa-crosshairs"></i> Gunakan Lokasi Saya
                </button>
              </div>

              <div class="form-group mt-3">
                <label>Status <span class="text-danger">*</span></label>
                <div class="row">
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_draft" 
                             value="draft" {{ old('status', 'draft') == 'draft' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_draft">Draft</label>
                    </div>
                    <small class="text-muted">Belum aktif</small>
                  </div>
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_aktif" 
                             value="aktif" {{ old('status') == 'aktif' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_aktif">Aktif</label>
                    </div>
                    <small class="text-muted">Mahasiswa bisa absen</small>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="catatan">Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3" 
                          placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
              </button>
              <a href="{{ route('sesi-absensi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
              </a>
            </div>
          </form>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Informasi</h3>
          </div>
          <div class="card-body">
            <h6><strong>Metode Absensi:</strong></h6>
            <ul class="pl-3">
              <li><strong>Manual:</strong> Dosen input status absensi secara manual</li>
              <li><strong>QR Code:</strong> Sistem generate QR Code unik, mahasiswa scan</li>
              <li><strong>Geolocation:</strong> Validasi lokasi mahasiswa dengan GPS</li>
            </ul>
            <hr>
            <h6><strong>Status Sesi:</strong></h6>
            <ul class="pl-3">
              <li><strong>Draft:</strong> Sesi belum aktif, mahasiswa belum bisa absen</li>
              <li><strong>Aktif:</strong> Sesi aktif, mahasiswa bisa melakukan absensi</li>
            </ul>
            <hr>
            <p class="mb-0"><small class="text-muted">
              <i class="fas fa-info-circle"></i> 
              Jika status <strong>Aktif</strong>, sistem akan otomatis membuat record absensi untuk semua mahasiswa di kelas.
            </small></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('AdminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
  $(function () {
    $('.select2').select2({
      theme: 'bootstrap4',
      width: '100%'
    });

    // Toggle geolocation fields
    function toggleGeolocationFields() {
      if ($('#metode_geo').is(':checked')) {
        $('#geolocation-fields').slideDown();
      } else {
        $('#geolocation-fields').slideUp();
      }
    }

    $('input[name="metode"]').change(toggleGeolocationFields);
    toggleGeolocationFields();
  });

  // Get current location
  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        $('#latitude').val(position.coords.latitude);
        $('#longitude').val(position.coords.longitude);
        alert('Lokasi berhasil diambil!');
      }, function(error) {
        alert('Gagal mengambil lokasi: ' + error.message);
      });
    } else {
      alert('Browser tidak mendukung Geolocation');
    }
  }
</script>
@endpush

@extends('layouts.master')

@section('title','Edit Sesi Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  #geolocation-fields { display: none; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Edit Sesi Absensi</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sesi-absensi.index') }}">Sesi Absensi</a></li>
        <li class="breadcrumb-item active">Edit</li>
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
            <h3 class="card-title mb-0">Form Edit Sesi Absensi</h3>
          </div>

          <form action="{{ route('sesi-absensi.update', $sesiAbsensi->id) }}" method="POST">
            @csrf
            @method('PUT')
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
                  @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ old('kelas_id', $sesiAbsensi->kelas_id) == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} - {{ $k->mataKuliah ? $k->mataKuliah->nama_mk : 'Mata Kuliah Tidak Ditemukan' }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" 
                           value="{{ old('tanggal', $sesiAbsensi->tanggal->format('Y-m-d')) }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="pertemuan_ke">Pertemuan Ke-</label>
                    <input type="number" name="pertemuan_ke" id="pertemuan_ke" class="form-control" 
                           value="{{ old('pertemuan_ke', $sesiAbsensi->pertemuan_ke) }}" min="1">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="topik">Topik Perkuliahan</label>
                <input type="text" name="topik" id="topik" class="form-control" 
                       value="{{ old('topik', $sesiAbsensi->topik) }}">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="waktu_mulai">Waktu Mulai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_mulai" id="waktu_mulai" class="form-control" 
                           value="{{ old('waktu_mulai', $sesiAbsensi->waktu_mulai->format('Y-m-d\TH:i')) }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="waktu_selesai">Waktu Selesai <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu_selesai" id="waktu_selesai" class="form-control" 
                           value="{{ old('waktu_selesai', $sesiAbsensi->waktu_selesai->format('Y-m-d\TH:i')) }}" required>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Metode Absensi <span class="text-danger">*</span></label>
                <div class="row">
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_manual" 
                             value="manual" {{ old('metode', $sesiAbsensi->metode) == 'manual' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_manual">Manual</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_qr" 
                             value="qr_code" {{ old('metode', $sesiAbsensi->metode) == 'qr_code' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_qr">QR Code</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="metode" id="metode_geo" 
                             value="geolocation" {{ old('metode', $sesiAbsensi->metode) == 'geolocation' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="metode_geo">Geolocation</label>
                    </div>
                  </div>
                </div>
              </div>

              <div id="geolocation-fields">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="latitude">Latitude</label>
                      <input type="number" step="0.00000001" name="latitude" id="latitude" class="form-control" 
                             value="{{ old('latitude', $sesiAbsensi->latitude) }}">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="longitude">Longitude</label>
                      <input type="number" step="0.00000001" name="longitude" id="longitude" class="form-control" 
                             value="{{ old('longitude', $sesiAbsensi->longitude) }}">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="radius_meter">Radius (meter)</label>
                      <input type="number" name="radius_meter" id="radius_meter" class="form-control" 
                             value="{{ old('radius_meter', $sesiAbsensi->radius_meter) }}" min="1">
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <div class="row">
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_draft" 
                             value="draft" {{ old('status', $sesiAbsensi->status) == 'draft' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_draft">Draft</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_aktif" 
                             value="aktif" {{ old('status', $sesiAbsensi->status) == 'aktif' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_aktif">Aktif</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_selesai" 
                             value="selesai" {{ old('status', $sesiAbsensi->status) == 'selesai' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_selesai">Selesai</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="custom-control custom-radio">
                      <input class="custom-control-input" type="radio" name="status" id="status_dibatalkan" 
                             value="dibatalkan" {{ old('status', $sesiAbsensi->status) == 'dibatalkan' ? 'checked' : '' }}>
                      <label class="custom-control-label" for="status_dibatalkan">Dibatalkan</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="catatan">Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3">{{ old('catatan', $sesiAbsensi->catatan) }}</textarea>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
              </button>
              <a href="{{ route('sesi-absensi.show', $sesiAbsensi->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
              </a>
            </div>
          </form>
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
</script>
@endpush

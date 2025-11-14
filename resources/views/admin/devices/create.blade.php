@extends('layouts.master')

@section('title','Tambah Perangkat')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Tambah Perangkat</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Perangkat</a></li>
        <li class="breadcrumb-item active">Tambah</li>
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
            <h3 class="card-title mb-0">Form Registrasi Perangkat</h3>
          </div>

          <form action="{{ route('devices.store') }}" method="POST">
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

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="device_id">Device ID <span class="text-danger">*</span></label>
                    <input type="text" name="device_id" id="device_id" class="form-control" 
                           placeholder="MAC Address / Serial Number" value="{{ old('device_id') }}" required>
                    <small class="text-muted">Contoh: AA:BB:CC:DD:EE:FF atau ESP32-001</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="device_name">Nama Perangkat <span class="text-danger">*</span></label>
                    <input type="text" name="device_name" id="device_name" class="form-control" 
                           placeholder="Contoh: Fingerprint Ruang 101" value="{{ old('device_name') }}" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="device_type">Tipe Perangkat <span class="text-danger">*</span></label>
                    <select name="device_type" id="device_type" class="form-control" required>
                      <option value="">Pilih Tipe</option>
                      <option value="fingerprint" {{ old('device_type') == 'fingerprint' ? 'selected' : '' }}>Fingerprint</option>
                      <option value="camera" {{ old('device_type') == 'camera' ? 'selected' : '' }}>Camera (OV2640)</option>
                      <option value="hybrid" {{ old('device_type') == 'hybrid' ? 'selected' : '' }}>Hybrid (Fingerprint + Camera)</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" class="form-control" 
                           placeholder="ESP32, ESP32-CAM, dll" value="{{ old('model') }}">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="location">Lokasi</label>
                <input type="text" name="location" id="location" class="form-control" 
                       placeholder="Contoh: Ruang Kelas 101, Gedung A" value="{{ old('location') }}">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="ip_address">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" 
                           placeholder="192.168.1.100" value="{{ old('ip_address') }}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="mac_address">MAC Address</label>
                    <input type="text" name="mac_address" id="mac_address" class="form-control" 
                           placeholder="AA:BB:CC:DD:EE:FF" value="{{ old('mac_address') }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="firmware_version">Versi Firmware</label>
                    <input type="text" name="firmware_version" id="firmware_version" class="form-control" 
                           placeholder="v1.0.0" value="{{ old('firmware_version') }}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                      <option value="active" {{ old('status', 'inactive') == 'active' ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ old('status', 'inactive') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                      <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                      <option value="error" {{ old('status') == 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="notes">Catatan</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" 
                          placeholder="Catatan tambahan tentang perangkat...">{{ old('notes') }}</textarea>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
              </button>
              <a href="{{ route('devices.index') }}" class="btn btn-secondary">
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
            <p><strong>Tipe Perangkat:</strong></p>
            <ul class="pl-3">
              <li><strong>Fingerprint:</strong> Sensor sidik jari</li>
              <li><strong>Camera:</strong> Kamera OV2640 untuk face recognition</li>
              <li><strong>Hybrid:</strong> Kombinasi fingerprint + camera</li>
            </ul>
            <hr>
            <p><strong>Device ID:</strong></p>
            <p><small class="text-muted">Gunakan MAC Address atau Serial Number unik dari perangkat ESP32</small></p>
            <hr>
            <p class="mb-0"><small class="text-muted">
              <i class="fas fa-info-circle"></i> 
              Field dengan tanda <span class="text-danger">*</span> wajib diisi
            </small></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

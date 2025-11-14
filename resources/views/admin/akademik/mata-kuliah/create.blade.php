@extends('layouts.master')

@section('title','Tambah Mata Kuliah')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Tambah Mata Kuliah</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mata-kuliah.index') }}">Mata Kuliah</a></li>
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
            <h3 class="card-title mb-0">Form Tambah Mata Kuliah</h3>
          </div>

          <form action="{{ route('mata-kuliah.store') }}" method="POST">
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
                    <label for="kode_mk">Kode Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="kode_mk" id="kode_mk" class="form-control" 
                           placeholder="Contoh: TIF101" value="{{ old('kode_mk') }}" required maxlength="20">
                    <small class="text-muted">Kode unik mata kuliah</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="sks">SKS <span class="text-danger">*</span></label>
                    <input type="number" name="sks" id="sks" class="form-control" 
                           placeholder="3" value="{{ old('sks', 3) }}" required min="1" max="6">
                    <small class="text-muted">Jumlah SKS (1-6)</small>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                <input type="text" name="nama_mk" id="nama_mk" class="form-control" 
                       placeholder="Contoh: Pemrograman Web" value="{{ old('nama_mk') }}" required>
              </div>

              <div class="form-group">
                <label for="semester">Semester</label>
                <select name="semester" id="semester" class="form-control">
                  <option value="">Pilih Semester (Opsional)</option>
                  @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                  @endfor
                </select>
                <small class="text-muted">Semester yang disarankan (1-8)</small>
              </div>

              <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" 
                          placeholder="Deskripsi mata kuliah...">{{ old('deskripsi') }}</textarea>
              </div>

              <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_aktif" 
                         value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_aktif">Aktif</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_nonaktif" 
                         value="nonaktif" {{ old('status') == 'nonaktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_nonaktif">Nonaktif</label>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
              </button>
              <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary">
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
            <p><strong>Contoh Kode MK:</strong></p>
            <ul class="pl-3">
              <li>TIF101 - Teknik Informatika</li>
              <li>SI201 - Sistem Informasi</li>
              <li>MTK301 - Matematika</li>
            </ul>
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

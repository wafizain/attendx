@extends('layouts.master')

@section('title','Edit Mata Kuliah')

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
      <h1 class="h4 mb-0">Edit Mata Kuliah</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mata-kuliah.index') }}">Mata Kuliah</a></li>
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
            <h3 class="card-title mb-0">Form Edit Mata Kuliah</h3>
          </div>

          <form action="{{ route('mata-kuliah.update', $mataKuliah->id) }}" method="POST">
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

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="kode_mk">Kode Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="kode_mk" id="kode_mk" class="form-control" 
                           value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" required maxlength="20">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="sks">SKS <span class="text-danger">*</span></label>
                    <input type="number" name="sks" id="sks" class="form-control" 
                           value="{{ old('sks', $mataKuliah->sks) }}" required min="1" max="6">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                <input type="text" name="nama_mk" id="nama_mk" class="form-control" 
                       value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" required>
              </div>

              <div class="form-group">
                <label for="semester">Semester</label>
                <select name="semester" id="semester" class="form-control">
                  <option value="">Pilih Semester (Opsional)</option>
                  @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ old('semester', $mataKuliah->semester) == $i ? 'selected' : '' }}>
                      Semester {{ $i }}
                    </option>
                  @endfor
                </select>
              </div>

              <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4">{{ old('deskripsi', $mataKuliah->deskripsi) }}</textarea>
              </div>

              <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_aktif" 
                         value="aktif" {{ old('status', $mataKuliah->status) == 'aktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_aktif">Aktif</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_nonaktif" 
                         value="nonaktif" {{ old('status', $mataKuliah->status) == 'nonaktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_nonaktif">Nonaktif</label>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
              </button>
              <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary">
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

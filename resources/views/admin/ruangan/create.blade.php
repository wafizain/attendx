@extends('layouts.master')

@section('title', 'Tambah Ruangan')
@section('page-title', 'Tambah Ruangan')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Form Tambah Ruangan</h5>
    </div>
    
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formRuanganCreate" action="{{ route('ruangan.store') }}" method="POST">
            @csrf
            
            <!-- Hidden status field to set default status to aktif -->
            <input type="hidden" name="status" value="aktif">
            
            <div class="row">
                <!-- Kode Ruangan -->
                <div class="col-md-6 mb-3">
                    <label for="kode" class="form-label">Kode Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="kode" id="kode" class="form-control @error('kode') is-invalid @enderror" 
                           value="{{ old('kode') }}" placeholder="Contoh: R101, LAB-A" required autofocus>
                    <small class="text-muted">Kode unik untuk ruangan (maksimal 20 karakter)</small>
                    @error('kode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nama Ruangan -->
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" 
                           value="{{ old('nama') }}" placeholder="Contoh: Ruang Kuliah 101" required>
                    <small class="text-muted">Nama lengkap ruangan (maksimal 100 karakter)</small>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kapasitas -->
                <div class="col-md-6 mb-3">
                    <label for="kapasitas" class="form-label">Kapasitas <span class="text-danger">*</span></label>
                    <input type="number" name="kapasitas" id="kapasitas" class="form-control @error('kapasitas') is-invalid @enderror" 
                           value="{{ old('kapasitas', 40) }}" min="1" max="500" required>
                    <small class="text-muted">Jumlah maksimal mahasiswa yang dapat ditampung</small>
                    @error('kapasitas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Lokasi -->
                <div class="col-md-6 mb-3">
                    <label for="lokasi" class="form-label">Lokasi/Gedung</label>
                    <input type="text" name="lokasi" id="lokasi" class="form-control @error('lokasi') is-invalid @enderror" 
                           value="{{ old('lokasi') }}" placeholder="Contoh: Gedung A Lantai 1">
                    <small class="text-muted">Lokasi atau gedung tempat ruangan berada</small>
                    @error('lokasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="col-md-12 mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                              rows="3" placeholder="Catatan tambahan tentang ruangan (opsional)">{{ old('keterangan') }}</textarea>
                    <small class="text-muted">Informasi tambahan seperti fasilitas, kondisi, atau catatan khusus</small>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;">
                    <i class="fas fa-save me-2"></i>Simpan Ruangan
                </button>
                <a href="{{ route('ruangan.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto uppercase kode ruangan
document.getElementById('kode').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// SweetAlert confirm submit
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('formRuanganCreate');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({
      title: 'Simpan data ruangan?',
      text: 'Pastikan data sudah benar sebelum disimpan.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, simpan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#0e4a95'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
</script>
@endpush
@endsection

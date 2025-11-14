@extends('layouts.master')

@section('title', 'Edit Dosen')
@section('page-title', 'Edit Dosen')

@push('styles')
  <style>
    .form-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      border: 1px solid #E5E7EB;
      overflow: hidden;
    }
    
    .form-card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #E5E7EB;
      background: white;
    }
    
    .form-card-body {
      padding: 1.5rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid #D1D5DB;
      padding: 0.625rem 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #3B82F6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-action {
      padding: 0.625rem 1.25rem;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
    
    .btn-action:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .section-divider {
      border-top: 2px solid #E5E7EB;
      margin: 1.5rem 0;
      padding-top: 1.5rem;
    }
    
    .section-title {
      font-size: 1rem;
      font-weight: 600;
      color: #111827;
      margin-bottom: 1rem;
      display: flex;
      align-items-center;
      gap: 0.5rem;
    }
    
    .alert-info-custom {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
  </style>
@endpush

@section('content')
  <!-- Alert Error -->
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Form Card -->
  <div class="form-card">
    <div class="form-card-header">
      <h5 class="mb-1 fw-semibold">Form Edit Dosen</h5>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">Perbarui data dosen dengan benar</p>
    </div>
    
    <div class="form-card-body">
      <form id="formDosenEdit" action="{{ route('admin.dosen.update', $dosen->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        
        
        <!-- NIDN -->
        <div class="mb-3">
          <label for="nidn" class="form-label">
            NIDN (Nomor Induk Dosen Nasional)
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('nidn') is-invalid @enderror" 
            id="nidn" 
            name="nidn"
            value="{{ old('nidn', $dosen->nidn) }}"
            placeholder="Contoh: 0123456789"
            required
            maxlength="20"
          >
          @error('nidn')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Masukkan NIDN dosen (10 digit)</div>
        </div>
        
        <!-- Nama Lengkap -->
        <div class="mb-3">
          <label for="nama" class="form-label">
            Nama Lengkap
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('nama') is-invalid @enderror" 
            id="nama" 
            name="nama"
            value="{{ old('nama', $dosen->nama) }}"
            placeholder="Nama lengkap dosen"
            required
            maxlength="100"
          >
          @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        
        
        
        
        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label">
            Email
            <span class="text-danger">*</span>
          </label>
          <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            id="email" 
            name="email"
            value="{{ old('email', $dosen->email) }}"
            placeholder="email@example.com"
            required
            maxlength="100"
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Email kontak dosen</div>
        </div>
        
        
        
        <!-- Jabatan Akademik -->
        <div class="mb-3">
          <label for="jabatan_akademik" class="form-label">
            Jabatan Akademik
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('jabatan_akademik') is-invalid @enderror" 
            id="jabatan_akademik" 
            name="jabatan_akademik"
            required
          >
            <option value="">-- Pilih Jabatan Akademik --</option>
            <option value="Asisten Ahli" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == 'Asisten Ahli' ? 'selected' : '' }}>Asisten Ahli</option>
            <option value="Lektor" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == 'Lektor' ? 'selected' : '' }}>Lektor</option>
            <option value="Lektor Kepala" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == 'Lektor Kepala' ? 'selected' : '' }}>Lektor Kepala</option>
            <option value="Guru Besar" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == 'Guru Besar' ? 'selected' : '' }}>Guru Besar</option>
            <option value="Tenaga Pengajar" {{ old('jabatan_akademik', $dosen->jabatan_akademik) == 'Tenaga Pengajar' ? 'selected' : '' }}>Tenaga Pengajar</option>
          </select>
          @error('jabatan_akademik')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2 justify-content-end">
          <button type="submit" class="btn btn-primary btn-action" style="background-color:#0e4a95;border-color:#0e4a95;">
            <i class="fas fa-save me-2"></i>
            Update
          </button>
          <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary btn-action" style="background-color:#6C757D;border-color:#6C757D;">
            <i class="fas fa-times me-2"></i>
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formDosenEdit');
      if (!form) return;
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Update data dosen?',
          text: 'Perubahan akan disimpan.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, update',
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

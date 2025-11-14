@extends('layouts.master')

@section('title', 'Tambah Kelas')
@section('page-title', 'Tambah Kelas')

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
      border-color: #10B981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
      <h5 class="mb-1 fw-semibold">Form Tambah Kelas</h5>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">Lengkapi data kelas dengan benar</p>
    </div>
    
    <div class="form-card-body">
      <form action="{{ route('kelas.store') }}" method="POST">
        @csrf
        
        <!-- Data Mata Kuliah -->
        <div class="section-title">
          <i class="fas fa-book text-primary"></i>
          Data Mata Kuliah
        </div>
        
        <!-- Mata Kuliah -->
        <div class="mb-3">
          <label for="mata_kuliah_id" class="form-label">
            Mata Kuliah
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('mata_kuliah_id') is-invalid @enderror" 
            id="mata_kuliah_id" 
            name="mata_kuliah_id"
            required
          >
            <option value="">-- Pilih Mata Kuliah --</option>
            @foreach($mataKuliahList as $mk)
              <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
              </option>
            @endforeach
          </select>
          @error('mata_kuliah_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <!-- Dosen Pengampu -->
        <div class="mb-3">
          <label for="dosen_id" class="form-label">
            Dosen Pengampu
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('dosen_id') is-invalid @enderror" 
            id="dosen_id" 
            name="dosen_id"
            required
          >
            <option value="">-- Pilih Dosen --</option>
            @foreach($dosenList as $dosen)
              <option value="{{ $dosen->id }}" {{ old('dosen_id') == $dosen->id ? 'selected' : '' }}>
                {{ $dosen->name }} @if($dosen->nidn)(NIDN: {{ $dosen->nidn }})@endif - {{ $dosen->prodi }}
              </option>
            @endforeach
          </select>
          @error('dosen_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="section-divider"></div>
        
        <!-- Data Kelas -->
        <div class="section-title">
          <i class="fas fa-chalkboard text-success"></i>
          Data Kelas
        </div>
        
        <!-- Nama Kelas -->
        <div class="mb-3">
          <label for="nama_kelas" class="form-label">
            Nama Kelas
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('nama_kelas') is-invalid @enderror" 
            id="nama_kelas" 
            name="nama_kelas"
            value="{{ old('nama_kelas') }}"
            placeholder="Contoh: TI-3A, SI-2B"
            required
            maxlength="255"
          >
          @error('nama_kelas')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Format: PRODI-SEMESTER-KELAS</div>
        </div>
        
        <!-- Tahun Ajaran -->
        <div class="mb-3">
          <label for="tahun_ajaran" class="form-label">
            Tahun Ajaran
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('tahun_ajaran') is-invalid @enderror" 
            id="tahun_ajaran" 
            name="tahun_ajaran"
            value="{{ old('tahun_ajaran') }}"
            placeholder="Contoh: 2024/2025"
            required
            maxlength="20"
          >
          @error('tahun_ajaran')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Format: YYYY/YYYY (contoh: 2024/2025)</div>
        </div>
        
        <!-- Semester -->
        <div class="mb-3">
          <label for="semester" class="form-label">
            Semester
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('semester') is-invalid @enderror" 
            id="semester" 
            name="semester"
            required
          >
            <option value="">-- Pilih Semester --</option>
            <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
            <option value="genap" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
          </select>
          @error('semester')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <!-- Ruangan -->
        <div class="mb-3">
          <label for="ruangan" class="form-label">
            Ruangan
          </label>
          <input 
            type="text" 
            class="form-control @error('ruangan') is-invalid @enderror" 
            id="ruangan" 
            name="ruangan"
            value="{{ old('ruangan') }}"
            placeholder="Contoh: Lab 301, Ruang A.1.2"
            maxlength="255"
          >
          @error('ruangan')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <!-- Kapasitas -->
        <div class="mb-3">
          <label for="kapasitas" class="form-label">
            Kapasitas Mahasiswa
            <span class="text-danger">*</span>
          </label>
          <input 
            type="number" 
            class="form-control @error('kapasitas') is-invalid @enderror" 
            id="kapasitas" 
            name="kapasitas"
            value="{{ old('kapasitas', 40) }}"
            min="1"
            max="200"
            required
          >
          @error('kapasitas')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Jumlah maksimal mahasiswa yang dapat mengikuti kelas ini (1-200)</div>
        </div>
        
        <!-- Status -->
        <div class="mb-4">
          <label for="status" class="form-label">
            Status
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('status') is-invalid @enderror" 
            id="status" 
            name="status"
            required
          >
            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
          </select>
          @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success btn-action">
            <i class="fas fa-save me-2"></i>
            Simpan
          </button>
          <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-action">
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
      const form = document.querySelector('form');
      
      // Validasi saat submit
      form.addEventListener('submit', function(e) {
        const kapasitas = document.getElementById('kapasitas').value;
        
        if (kapasitas < 1 || kapasitas > 200) {
          e.preventDefault();
          alert('Kapasitas harus antara 1 sampai 200 mahasiswa!');
          return false;
        }
        
        return true;
      });
    });
  </script>
@endpush

@extends('layouts.master')

@section('title', 'Edit Mahasiswa')
@section('page-title', 'Edit Mahasiswa')

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
      <h5 class="mb-1 fw-semibold">Form Edit Mahasiswa</h5>
      <p class="text-muted mb-0" style="font-size: 0.875rem;">Perbarui data mahasiswa dengan benar</p>
    </div>
    
    <div class="form-card-body">
      <form action="{{ route('mahasiswa.update', $mahasiswa->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Data Identitas -->
        <div class="section-title">
          <i class="fas fa-id-card text-primary"></i>
          Data Identitas
        </div>
        
        <!-- NIM -->
        <div class="mb-3">
          <label for="nim" class="form-label">
            NIM (Nomor Induk Mahasiswa)
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('nim') is-invalid @enderror" 
            id="nim" 
            name="nim"
            value="{{ old('nim', $mahasiswa->nim) }}"
            placeholder="Contoh: 2025010001"
            required
            maxlength="30"
          >
          @error('nim')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Masukkan NIM mahasiswa (10 digit)</div>
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
            value="{{ old('nama', $mahasiswa->nama) }}"
            placeholder="Nama lengkap mahasiswa"
            required
            maxlength="100"
          >
          @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="section-divider"></div>
        
        <!-- Data Kontak -->
        <div class="section-title">
          <i class="fas fa-envelope text-success"></i>
          Data Kontak
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
            value="{{ old('email', $mahasiswa->email) }}"
            placeholder="email@example.com"
            required
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Email akan digunakan untuk login</div>
        </div>
        
        <!-- No HP -->
        <div class="mb-3">
          <label for="no_hp" class="form-label">
            No. Handphone
          </label>
          <input 
            type="text" 
            class="form-control @error('no_hp') is-invalid @enderror" 
            id="no_hp" 
            name="no_hp"
            value="{{ old('no_hp', $mahasiswa->no_hp) }}"
            placeholder="08xxxxxxxxxx"
            maxlength="20"
          >
          @error('no_hp')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="section-divider"></div>
        
        <!-- Data Akademik -->
        <div class="section-title">
          <i class="fas fa-graduation-cap text-info"></i>
          Data Akademik
        </div>
        
        <!-- Kelas -->
        <div class="mb-3">
          <label for="kelas" class="form-label">
            Kelas
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('kelas') is-invalid @enderror" 
            id="kelas" 
            name="kelas"
            value="{{ old('kelas', $mahasiswa->kelas) }}"
            placeholder="Contoh: TI-3A, SI-2B"
            required
            maxlength="20"
          >
          @error('kelas')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Format: PRODI-SEMESTER-KELAS (contoh: TI-3A)</div>
        </div>
        
        <!-- Program Studi -->
        <div class="mb-3">
          <label for="prodi" class="form-label">
            Program Studi
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('prodi') is-invalid @enderror" 
            id="prodi" 
            name="prodi"
            required
          >
            <option value="">-- Pilih Program Studi --</option>
            <option value="Teknik Informatika" {{ old('prodi', $mahasiswa->prodi) == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
            <option value="Sistem Informasi" {{ old('prodi', $mahasiswa->prodi) == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
            <option value="Teknik Komputer" {{ old('prodi', $mahasiswa->prodi) == 'Teknik Komputer' ? 'selected' : '' }}>Teknik Komputer</option>
            <option value="Manajemen Informatika" {{ old('prodi', $mahasiswa->prodi) == 'Manajemen Informatika' ? 'selected' : '' }}>Manajemen Informatika</option>
          </select>
          @error('prodi')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <!-- Angkatan -->
        <div class="mb-3">
          <label for="angkatan" class="form-label">
            Angkatan
            <span class="text-danger">*</span>
          </label>
          <select 
            class="form-select @error('angkatan') is-invalid @enderror" 
            id="angkatan" 
            name="angkatan"
            required
          >
            <option value="">-- Pilih Angkatan --</option>
            @php
              $currentYear = date('Y');
              $startYear = 2020;
            @endphp
            @for($year = $currentYear + 1; $year >= $startYear; $year--)
              <option value="{{ $year }}" {{ old('angkatan', $mahasiswa->angkatan) == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endfor
          </select>
          @error('angkatan')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Tahun masuk mahasiswa</div>
        </div>
        
        <div class="section-divider"></div>
        
        <!-- Password Section -->
        <div class="section-title">
          <i class="fas fa-lock text-warning"></i>
          Ganti Password (Opsional)
        </div>
        
        <div class="alert-info-custom">
          <div class="d-flex align-items-start gap-2">
            <i class="fas fa-info-circle text-primary mt-1"></i>
            <div>
              <div class="fw-semibold text-primary mb-1" style="font-size: 0.875rem;">Ganti Password</div>
              <p class="text-muted mb-0" style="font-size: 0.8125rem;">Isi field password jika ingin mengganti password. Kosongkan jika tidak ingin mengubah password.</p>
            </div>
          </div>
        </div>

        <!-- Password Baru -->
        <div class="mb-3">
          <label for="password" class="form-label">
            Password Baru
            <span class="text-muted" style="font-size: 0.8125rem;">(opsional)</span>
          </label>
          <input 
            type="password" 
            class="form-control @error('password') is-invalid @enderror" 
            id="password" 
            name="password"
            placeholder="Minimal 8 karakter"
            autocomplete="new-password"
          >
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Minimal 8 karakter, kombinasi huruf dan angka</div>
        </div>
        
        <!-- Konfirmasi Password -->
        <div class="mb-4">
          <label for="password_confirmation" class="form-label">
            Konfirmasi Password Baru
            <span class="text-muted" style="font-size: 0.8125rem;">(opsional)</span>
          </label>
          <input 
            type="password" 
            class="form-control @error('password_confirmation') is-invalid @enderror" 
            id="password_confirmation" 
            name="password_confirmation"
            placeholder="Ulangi password baru"
            autocomplete="new-password"
          >
          @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Masukkan password yang sama untuk konfirmasi</div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-action">
            <i class="fas fa-save me-2"></i>
            Update
          </button>
          <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary btn-action">
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
      const passwordInput = document.getElementById('password');
      const passwordConfirmInput = document.getElementById('password_confirmation');
      
      // Validasi saat submit
      form.addEventListener('submit', function(e) {
        const newPass = passwordInput.value.trim();
        const confirmPass = passwordConfirmInput.value.trim();
        
        // Jika salah satu field password diisi
        if (newPass || confirmPass) {
          // Validate password fields if one is filled
          if ((newPass && !confirmPass) || (!newPass && confirmPass)) {
            e.preventDefault();
            alert('Untuk mengganti password, isi kedua field password baru dan konfirmasi password');
            return false;
          }
          
          // Validate password match
          if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak sama!');
            passwordConfirmInput.focus();
            return false;
          }
          
          // Validate password length
          if (newPass.length < 8) {
            e.preventDefault();
            alert('Password minimal 8 karakter!');
            passwordInput.focus();
            return false;
          }
        }
        
        return true;
      });
    });
  </script>
@endpush

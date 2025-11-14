@extends('layouts.master')

@section('title', 'Tambah Admin')
@section('page-title', 'Tambah Admin')

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
      font-size: 0.875rem;
      margin-bottom: 0.5rem;
    }
    
    .form-control {
      border-radius: 8px;
      border: 1px solid #E5E7EB;
      padding: 0.625rem 0.875rem;
      font-size: 0.875rem;
      transition: all 0.2s ease;
    }
    
    .form-control:focus {
      border-color: #10B981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
      outline: none;
    }
    
    .form-text {
      font-size: 0.8125rem;
      color: #6B7280;
      margin-top: 0.375rem;
    }
    
    .invalid-feedback {
      font-size: 0.8125rem;
      color: #EF4444;
      margin-top: 0.375rem;
    }
    
    .btn-action {
      padding: 0.625rem 1.25rem;
      font-size: 0.875rem;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
  </style>
@endpush

@section('content')
  <!-- Form Card -->
  <div class="form-card">
    <div class="form-card-header">
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.index') }}" class="btn btn-link text-dark p-0">
          <i class="fas fa-arrow-left"></i>
        </a>
        <div>
          <h5 class="mb-1 fw-semibold">Tambah Admin Baru</h5>
          <p class="text-muted mb-0" style="font-size: 0.875rem;">Isi form untuk menambahkan administrator baru</p>
        </div>
      </div>
    </div>
    
    <div class="form-card-body">
      <form action="{{ route('admin.store') }}" method="POST">
        @csrf
        
        <!-- Nama Lengkap -->
        <div class="mb-3">
          <label for="name" class="form-label">
            Nama Lengkap
            <span class="text-danger">*</span>
          </label>
          <input 
            type="text" 
            class="form-control @error('name') is-invalid @enderror" 
            id="name" 
            name="name" 
            value="{{ old('name') }}"
            placeholder="Masukkan nama lengkap"
            required
          >
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Nama lengkap administrator</div>
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
            value="{{ old('email') }}"
            placeholder="admin@example.com"
            required
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Email akan digunakan untuk login</div>
        </div>
        
        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label">
            Password
            <span class="text-danger">*</span>
          </label>
          <input 
            type="password" 
            class="form-control @error('password') is-invalid @enderror" 
            id="password" 
            name="password"
            placeholder="Minimal 8 karakter"
            required
          >
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Minimal 8 karakter, kombinasi huruf dan angka</div>
        </div>
        
        <!-- Konfirmasi Password -->
        <div class="mb-4">
          <label for="password_confirmation" class="form-label">
            Konfirmasi Password
            <span class="text-danger">*</span>
          </label>
          <input 
            type="password" 
            class="form-control @error('password_confirmation') is-invalid @enderror" 
            id="password_confirmation" 
            name="password_confirmation"
            placeholder="Ulangi password"
            required
          >
          @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Masukkan password yang sama untuk konfirmasi</div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success btn-action">
            <i class="fas fa-save me-2"></i>
            Simpan
          </button>
          <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-action">
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
        // Validate password match
        if (passwordInput.value !== passwordConfirmInput.value) {
          e.preventDefault();
          alert('Password dan konfirmasi password tidak sama!');
          passwordConfirmInput.focus();
          return false;
        }
        
        // Validate password length
        if (passwordInput.value.length < 8) {
          e.preventDefault();
          alert('Password minimal 8 karakter!');
          passwordInput.focus();
          return false;
        }
        
        return true;
      });
    });
  </script>
@endpush

@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Mata Kuliah</h1>
        <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('mata-kuliah.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kode Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mk" class="form-control @error('kode_mk') is-invalid @enderror" 
                                   value="{{ old('kode_mk') }}" placeholder="IF2101, TI301" required style="text-transform: uppercase;">
                            @error('kode_mk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: huruf/angka, titik, underscore, atau dash. 2-20 karakter. Akan otomatis diubah menjadi huruf besar.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mk" class="form-control @error('nama_mk') is-invalid @enderror" 
                                   value="{{ old('nama_mk') }}" placeholder="Struktur Data" required>
                            @error('nama_mk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <select name="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror" required>
                                <option value="">-- Pilih Prodi --</option>
                                @foreach($prodiList as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_prodi') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama }} ({{ $prodi->jenjang }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_prodi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Kurikulum <span class="text-danger">*</span></label>
                            <input type="text" name="kurikulum" class="form-control @error('kurikulum') is-invalid @enderror" 
                                   value="{{ old('kurikulum', '2024') }}" placeholder="2024, MBKM-2023" required>
                            @error('kurikulum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: 2024, MBKM-2023</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" class="form-control @error('sks') is-invalid @enderror" 
                                   value="{{ old('sks', 3) }}" min="1" max="6" required>
                            @error('sks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <input type="number" name="semester" class="form-control @error('semester') is-invalid @enderror" 
                                   value="{{ old('semester') }}" min="1" max="14" placeholder="1-14" required>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Semester 1-14</small>
                        </div>
                    </div>

                </div>

                

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                              rows="3" placeholder="Deskripsi mata kuliah...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Catatan:</strong> Kode mata kuliah harus unik untuk kombinasi Prodi + Kurikulum yang sama.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">Batal</a>
                    <button type="submit" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;" onclick="return confirmSubmit(event);">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmSubmit(event) {
    event.preventDefault();
    
    // Check if Swal is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        // Fallback to native confirm
        if (confirm('Apakah Anda yakin ingin menambahkan mata kuliah ini?')) {
            event.target.form.submit();
        }
        return false;
    }
    
    console.log('Form submit intercepted, showing SweetAlert');
    
    Swal.fire({
        title: 'Konfirmasi Tambah Mata Kuliah',
        text: "Apakah Anda yakin ingin menambahkan mata kuliah ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0e4a95',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tambah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        console.log('SweetAlert result:', result);
        if (result.isConfirmed) {
            console.log('Submitting form');
            event.target.form.submit();
        } else {
            console.log('Form submission cancelled');
        }
    });
    
    return false;
}
</script>
@endpush

@endsection

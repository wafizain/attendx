@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Mata Kuliah</h1>
        <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('mata-kuliah.update', $mataKuliah->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Kode Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mk" class="form-control @error('kode_mk') is-invalid @enderror" 
                                   value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" required style="text-transform: uppercase;">
                            @error('kode_mk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mk" class="form-control @error('nama_mk') is-invalid @enderror" 
                                   value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" required>
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
                                @foreach($prodiList as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_prodi', $mataKuliah->id_prodi) == $prodi->id ? 'selected' : '' }}>
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
                                   value="{{ old('kurikulum', $mataKuliah->kurikulum) }}" required>
                            @error('kurikulum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" class="form-control @error('sks') is-invalid @enderror" 
                                   value="{{ old('sks', $mataKuliah->sks) }}" min="1" max="6" required>
                            @error('sks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" name="semester" class="form-control @error('semester') is-invalid @enderror" 
                                   value="{{ old('semester', $mataKuliah->semester) }}" min="1" max="14">
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" 
                              rows="3">{{ old('deskripsi', $mataKuliah->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('mata-kuliah.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Swal is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        return;
    }
    
    // Check for success message in session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            confirmButtonColor: '#0e4a95',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    @endif
    
    // Check for error message in session
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session("error") }}',
            confirmButtonColor: '#0e4a95'
        });
    @endif
});
</script>
@endpush

@endsection

@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Duplikasi Mata Kuliah</h1>
        <a href="{{ route('mata-kuliah.show', $mataKuliah->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Duplikasi dari: {{ $mataKuliah->nama_mk }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Fitur duplikasi memudahkan Anda membuat mata kuliah yang sama untuk kurikulum baru. 
                        Deskripsi dan prasyarat akan disalin otomatis.
                    </div>

                    <form action="{{ route('mata-kuliah.store-duplicate', $mataKuliah->id) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_prodi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                    <select name="id_prodi" id="id_prodi" class="form-select @error('id_prodi') is-invalid @enderror" required>
                                        <option value="">Pilih Prodi</option>
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

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kurikulum" class="form-label">Kurikulum <span class="text-danger">*</span></label>
                                    <input type="text" name="kurikulum" id="kurikulum" class="form-control @error('kurikulum') is-invalid @enderror" 
                                           value="{{ old('kurikulum', date('Y')) }}" placeholder="2024" required>
                                    <small class="text-muted">Contoh: 2024, 2024-Ganjil, K2024</small>
                                    @error('kurikulum')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="kode_mk" class="form-label">Kode MK <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_mk" id="kode_mk" class="form-control text-uppercase @error('kode_mk') is-invalid @enderror" 
                                           value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" placeholder="IF2101" required>
                                    <small class="text-muted">Huruf kapital, angka, titik, strip</small>
                                    @error('kode_mk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_mk" class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mk" id="nama_mk" class="form-control @error('nama_mk') is-invalid @enderror" 
                                           value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" placeholder="Struktur Data" required>
                                    @error('nama_mk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="sks" class="form-label">SKS <span class="text-danger">*</span></label>
                                    <input type="number" name="sks" id="sks" class="form-control @error('sks') is-invalid @enderror" 
                                           value="{{ old('sks', $mataKuliah->sks) }}" min="1" max="6" required>
                                    @error('sks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="semester_rekomendasi" class="form-label">Semester Rekomendasi</label>
                                    <select name="semester_rekomendasi" id="semester_rekomendasi" class="form-select @error('semester_rekomendasi') is-invalid @enderror">
                                        <option value="">Tidak ditentukan</option>
                                        @for($i = 1; $i <= 8; $i++)
                                            <option value="{{ $i }}" {{ old('semester_rekomendasi', $mataKuliah->semester_rekomendasi) == $i ? 'selected' : '' }}>
                                                Semester {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('semester_rekomendasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary">
                            <strong>Akan disalin otomatis:</strong>
                            <ul class="mb-0">
                                @if($mataKuliah->deskripsi)
                                    <li>Deskripsi</li>
                                @endif
                                @if($mataKuliah->prasyarat)
                                    <li>Prasyarat</li>
                                @endif
                                @if($mataKuliah->kode_eksternal)
                                    <li>Kode Eksternal</li>
                                @endif
                                @if(!$mataKuliah->deskripsi && !$mataKuliah->prasyarat && !$mataKuliah->kode_eksternal)
                                    <li class="text-muted">Tidak ada data tambahan yang akan disalin</li>
                                @endif
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('mata-kuliah.show', $mataKuliah->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-copy"></i> Duplikasi Mata Kuliah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Data Asli</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Kode</th>
                            <td>{{ $mataKuliah->kode_mk }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $mataKuliah->nama_mk }}</td>
                        </tr>
                        <tr>
                            <th>Prodi</th>
                            <td>{{ $mataKuliah->prodi->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kurikulum</th>
                            <td><span class="badge bg-secondary">{{ $mataKuliah->kurikulum }}</span></td>
                        </tr>
                        <tr>
                            <th>SKS</th>
                            <td>{{ $mataKuliah->sks }}</td>
                        </tr>
                        <tr>
                            <th>Semester</th>
                            <td>{{ $mataKuliah->semester_rekomendasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge bg-{{ $mataKuliah->status_badge }}">{{ $mataKuliah->status_label }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto uppercase kode MK
document.getElementById('kode_mk').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endpush
@endsection

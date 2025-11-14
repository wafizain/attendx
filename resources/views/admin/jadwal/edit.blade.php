@extends('layouts.master')

@section('title', 'Edit Jadwal Kuliah')
@section('page-title', 'Edit Jadwal Kuliah')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Jadwal Kuliah</h1>
        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Ada masalah dengan input Anda:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

        <form action="{{ route('jadwal.update', $jadwal->id) }}" method="POST" id="jadwalEditForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Mata Kuliah -->
                <div class="col-md-6 mb-3">
                    <label for="id_mk" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                    <select name="id_mk" id="id_mk" class="form-select @error('id_mk') is-invalid @enderror" required>
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach($mkList as $mk)
                            <option value="{{ $mk->id }}" {{ (old('id_mk', $jadwal->id_mk) == $mk->id) ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                            </option>
                        @endforeach
                    </select>
                    @error('id_mk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Dosen -->
                <div class="col-md-6 mb-3">
                    <label for="id_dosen" class="form-label">Dosen Pengampu <span class="text-danger">*</span></label>
                    <select name="id_dosen" id="id_dosen" class="form-select @error('id_dosen') is-invalid @enderror" required>
                        <option value="">Pilih Dosen</option>
                        @foreach($dosenList as $dosen)
                            <option value="{{ $dosen->id }}" {{ (old('id_dosen', $jadwal->id_dosen) == $dosen->id) ? 'selected' : '' }}>
                                {{ $dosen->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_dosen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kelas (Opsional) -->
                <div class="col-md-6 mb-3">
                    <label for="id_kelas" class="form-label">Kelas Administratif (Opsional)</label>
                    <select name="id_kelas" id="id_kelas" class="form-select @error('id_kelas') is-invalid @enderror">
                        <option value="">Tidak Terikat Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (old('id_kelas', $jadwal->id_kelas) == $kelas->id) ? 'selected' : '' }}>
                                {{ $kelas->kode }} - {{ $kelas->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Paralel -->
                <div class="col-md-6 mb-3">
                    <label for="paralel" class="form-label">Kode Paralel (Opsional)</label>
                    <input type="text" name="paralel" id="paralel" class="form-control @error('paralel') is-invalid @enderror" 
                           value="{{ old('paralel', $jadwal->paralel) }}" placeholder="A, B, C, dst" maxlength="5">
                    <small class="text-muted">Contoh: A, B, C untuk kelas paralel</small>
                    @error('paralel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Waktu & Ruangan</h6>

            <div class="row">
                <!-- Hari -->
                <div class="col-md-3 mb-3">
                    <label for="hari" class="form-label">Hari <span class="text-danger">*</span></label>
                    <select name="hari" id="hari" class="form-select @error('hari') is-invalid @enderror" required>
                        <option value="">Pilih Hari</option>
                        <option value="1" {{ (old('hari', $jadwal->hari) == '1') ? 'selected' : '' }}>Senin</option>
                        <option value="2" {{ (old('hari', $jadwal->hari) == '2') ? 'selected' : '' }}>Selasa</option>
                        <option value="3" {{ (old('hari', $jadwal->hari) == '3') ? 'selected' : '' }}>Rabu</option>
                        <option value="4" {{ (old('hari', $jadwal->hari) == '4') ? 'selected' : '' }}>Kamis</option>
                        <option value="5" {{ (old('hari', $jadwal->hari) == '5') ? 'selected' : '' }}>Jumat</option>
                        <option value="6" {{ (old('hari', $jadwal->hari) == '6') ? 'selected' : '' }}>Sabtu</option>
                        <option value="7" {{ (old('hari', $jadwal->hari) == '7') ? 'selected' : '' }}>Minggu</option>
                    </select>
                    @error('hari')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jam Mulai -->
                <div class="col-md-3 mb-3">
                    <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" 
                           value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}" required>
                    @error('jam_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jam Selesai -->
                <div class="col-md-3 mb-3">
                    <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" 
                           value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" required>
                    @error('jam_selesai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Ruangan -->
                <div class="col-md-3 mb-3">
                    <label for="id_ruangan" class="form-label">Ruangan <span class="text-danger">*</span></label>
                    <select name="id_ruangan" id="id_ruangan" class="form-select @error('id_ruangan') is-invalid @enderror" required>
                        <option value="">Pilih Ruangan</option>
                        @foreach($ruanganList as $ruangan)
                            <option value="{{ $ruangan->id }}" {{ (old('id_ruangan', $jadwal->id_ruangan) == $ruangan->id) ? 'selected' : '' }}>
                                {{ $ruangan->kode }} - {{ $ruangan->nama }} ({{ $ruangan->kapasitas }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Periode Semester</h6>

            <div class="row">
                <!-- Tanggal Mulai -->
                <div class="col-md-6 mb-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                           value="{{ old('tanggal_mulai', $jadwal->tanggal_mulai->format('Y-m-d')) }}" required>
                    @error('tanggal_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tanggal Selesai -->
                <div class="col-md-6 mb-3">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                           value="{{ old('tanggal_selesai', $jadwal->tanggal_selesai->format('Y-m-d')) }}" required>
                    @error('tanggal_selesai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Pertemuan yang sudah digenerate tidak akan otomatis diupdate. Untuk mengubah pertemuan, edit secara manual di halaman detail.
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Aturan Absensi</h6>

            <div class="row">
                <!-- Absen Open Min -->
                <div class="col-md-4 mb-3">
                    <label for="absen_open_min" class="form-label">Buka Absensi (menit sebelum)</label>
                    <input type="number" name="absen_open_min" id="absen_open_min" class="form-control @error('absen_open_min') is-invalid @enderror" 
                           value="{{ old('absen_open_min', $jadwal->absen_open_min) }}" min="0" max="60">
                    <small class="text-muted">Menit sebelum jam mulai</small>
                    @error('absen_open_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Grace Late Min -->
                <div class="col-md-4 mb-3">
                    <label for="grace_late_min" class="form-label">Toleransi Telat (menit)</label>
                    <input type="number" name="grace_late_min" id="grace_late_min" class="form-control @error('grace_late_min') is-invalid @enderror" 
                           value="{{ old('grace_late_min', $jadwal->grace_late_min) }}" min="0" max="60">
                    <small class="text-muted">Menit dari jam mulai</small>
                    @error('grace_late_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Absen Close Min -->
                <div class="col-md-4 mb-3">
                    <label for="absen_close_min" class="form-label">Tutup Absensi (menit setelah)</label>
                    <input type="number" name="absen_close_min" id="absen_close_min" class="form-control @error('absen_close_min') is-invalid @enderror" 
                           value="{{ old('absen_close_min', $jadwal->absen_close_min) }}" min="0" max="120">
                    <small class="text-muted">Menit setelah jam mulai</small>
                    @error('absen_close_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Wajah Wajib -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="wajah_wajib" id="wajah_wajib" class="form-check-input" value="1" 
                               {{ old('wajah_wajib', $jadwal->wajah_wajib) ? 'checked' : '' }}>
                        <label for="wajah_wajib" class="form-check-label">
                            Wajib Foto Wajah saat Verifikasi Fingerprint
                        </label>
                    </div>
                    <small class="text-muted">Jika dicentang, mahasiswa harus mengambil foto wajah saat scan fingerprint</small>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Status & Catatan</h6>

            <div class="row">
                <!-- Status -->
                <div class="col-md-12 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="aktif" {{ (old('status', $jadwal->status) == 'aktif') ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ (old('status', $jadwal->status) == 'nonaktif') ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Catatan -->
                <div class="col-md-12 mb-3">
                    <label for="catatan" class="form-label">Catatan (Opsional)</label>
                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $jadwal->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('jadwal.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="button" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;" onclick="confirmUpdate()">
                    <i class="fas fa-save me-2"></i>Update Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// SweetAlert confirmation for form update
function confirmUpdate() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Update Jadwal?',
            text: 'Apakah Anda yakin ingin mengupdate jadwal kuliah ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update',
            confirmButtonColor: '#0e4a95',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('jadwalEditForm').submit();
            }
        });
    } else {
        // Fallback if SweetAlert2 not available
        document.getElementById('jadwalEditForm').submit();
    }
}

// Flash messages
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = @json(session('success'));
    const flashError = @json(session('error'));
    if (typeof Swal !== 'undefined') {
        if (flashSuccess) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: flashSuccess, confirmButtonColor: '#0e4a95' });
        } else if (flashError) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: flashError, confirmButtonColor: '#0e4a95' });
        }
    }
});
</script>
@endpush

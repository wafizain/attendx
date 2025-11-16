@extends('layouts.master')

@section('title', 'Tambah Jadwal Kuliah')
@section('page-title', 'Tambah Jadwal Kuliah')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Jadwal Kuliah</h1>
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

        <form action="{{ route('jadwal.store') }}" method="POST" id="jadwalCreateForm">
            @csrf
            
            <div class="row">
                <!-- Mata Kuliah -->
                <div class="col-md-6 mb-3">
                    <label for="id_mk" class="form-label">Mata Kuliah <span class="text-danger">*</span></label>
                    <select name="id_mk" id="id_mk" class="form-select @error('id_mk') is-invalid @enderror" required>
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach($mkList as $mk)
                            <option value="{{ $mk->id }}" {{ old('id_mk') == $mk->id ? 'selected' : '' }}>
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
                            <option value="{{ $dosen->id }}" {{ old('id_dosen') == $dosen->id ? 'selected' : '' }}>
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
                            <option value="{{ $kelas->id }}" {{ old('id_kelas') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->kode }} - {{ $kelas->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Semester -->
                <div class="col-md-6 mb-3">
                    <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                        <option value="">Pilih Semester</option>
                        @foreach($semesterList as $semester)
                            <option value="{{ $semester->id }}" 
                                    data-jumlah="{{ $semester->jumlah_pertemuan }}"
                                    data-uts="{{ $semester->pertemuan_uts }}"
                                    data-uas="{{ $semester->pertemuan_uas }}"
                                    {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->tahun_ajaran }} - Semester {{ $semester->semester == 1 ? 'Ganjil' : 'Genap' }}
                                @if($semester->status == 'aktif') (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Jumlah pertemuan akan otomatis terisi sesuai semester</small>
                    @error('semester_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Paralel -->
                <div class="col-md-6 mb-3">
                    <label for="paralel" class="form-label">Kode Paralel (Opsional)</label>
                    <input type="text" name="paralel" id="paralel" class="form-control @error('paralel') is-invalid @enderror" 
                           value="{{ old('paralel') }}" placeholder="A, B, C, dst" maxlength="5">
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
                        <option value="1" {{ old('hari') == '1' ? 'selected' : '' }}>Senin</option>
                        <option value="2" {{ old('hari') == '2' ? 'selected' : '' }}>Selasa</option>
                        <option value="3" {{ old('hari') == '3' ? 'selected' : '' }}>Rabu</option>
                        <option value="4" {{ old('hari') == '4' ? 'selected' : '' }}>Kamis</option>
                        <option value="5" {{ old('hari') == '5' ? 'selected' : '' }}>Jumat</option>
                        <option value="6" {{ old('hari') == '6' ? 'selected' : '' }}>Sabtu</option>
                        <option value="7" {{ old('hari') == '7' ? 'selected' : '' }}>Minggu</option>
                    </select>
                    @error('hari')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jam Mulai -->
                <div class="col-md-3 mb-3">
                    <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" 
                           value="{{ old('jam_mulai', '08:00') }}" required>
                    @error('jam_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jam Selesai -->
                <div class="col-md-3 mb-3">
                    <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" 
                           value="{{ old('jam_selesai', '10:00') }}" required>
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
                            <option value="{{ $ruangan->id }}" {{ old('id_ruangan') == $ruangan->id ? 'selected' : '' }}>
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
            <h6 class="mb-3">Konfigurasi Semester</h6>

            <div class="row">
                <!-- Info Semester -->
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info" id="semesterInfo" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi Semester yang Dipilih:</strong>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-check fa-2x text-primary me-3"></i>
                                    <div>
                                        <small class="text-muted">Jumlah Pertemuan</small>
                                        <h5 class="mb-0" id="infoJumlah">-</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-list fa-2x text-warning me-3"></i>
                                    <div>
                                        <small class="text-muted">UTS</small>
                                        <h5 class="mb-0">Pertemuan ke-<span id="infoUTS">-</span></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-graduation-cap fa-2x text-danger me-3"></i>
                                    <div>
                                        <small class="text-muted">UAS</small>
                                        <h5 class="mb-0">Pertemuan ke-<span id="infoUAS">-</span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning" id="semesterWarning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Silakan pilih semester terlebih dahulu untuk melihat konfigurasi pertemuan.
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Aturan Absensi</h6>

            <div class="row">
                <!-- Absen Open Min -->
                <div class="col-md-4 mb-3">
                    <label for="absen_open_min" class="form-label">Buka Absensi (menit sebelum)</label>
                    <input type="number" name="absen_open_min" id="absen_open_min" class="form-control @error('absen_open_min') is-invalid @enderror" 
                           value="{{ old('absen_open_min', 10) }}" min="0" max="60">
                    <small class="text-muted">Default: 10 menit sebelum jam mulai</small>
                    @error('absen_open_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Grace Late Min -->
                <div class="col-md-4 mb-3">
                    <label for="grace_late_min" class="form-label">Toleransi Telat (menit)</label>
                    <input type="number" name="grace_late_min" id="grace_late_min" class="form-control @error('grace_late_min') is-invalid @enderror" 
                           value="{{ old('grace_late_min', 15) }}" min="0" max="60">
                    <small class="text-muted">Default: 15 menit dari jam mulai</small>
                    @error('grace_late_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Absen Close Min -->
                <div class="col-md-4 mb-3">
                    <label for="absen_close_min" class="form-label">Tutup Absensi (menit setelah)</label>
                    <input type="number" name="absen_close_min" id="absen_close_min" class="form-control @error('absen_close_min') is-invalid @enderror" 
                           value="{{ old('absen_close_min', 30) }}" min="0" max="120">
                    <small class="text-muted">Default: 30 menit setelah jam mulai</small>
                    @error('absen_close_min')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Wajah Wajib -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="wajah_wajib" id="wajah_wajib" class="form-check-input" value="1" {{ old('wajah_wajib') ? 'checked' : '' }}>
                        <label for="wajah_wajib" class="form-check-label">
                            Wajib Foto Wajah saat Verifikasi Fingerprint
                        </label>
                    </div>
                    <small class="text-muted">Jika dicentang, mahasiswa harus mengambil foto wajah saat scan fingerprint</small>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="mb-3">Catatan</h6>

            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan') }}</textarea>
                @error('catatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('jadwal.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="button" class="btn btn-primary" style="background-color:#0e4a95;border-color:#0e4a95;" onclick="confirmSubmit()">
                    <i class="fas fa-save me-2"></i>Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto-fill semester info when semester is selected
document.getElementById('semester_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const semesterInfo = document.getElementById('semesterInfo');
    const semesterWarning = document.getElementById('semesterWarning');
    
    if (this.value) {
        const jumlah = selectedOption.getAttribute('data-jumlah');
        const uts = selectedOption.getAttribute('data-uts');
        const uas = selectedOption.getAttribute('data-uas');
        
        document.getElementById('infoJumlah').textContent = jumlah + ' pertemuan';
        document.getElementById('infoUTS').textContent = uts || '-';
        document.getElementById('infoUAS').textContent = uas || '-';
        
        semesterInfo.style.display = 'block';
        semesterWarning.style.display = 'none';
    } else {
        semesterInfo.style.display = 'none';
        semesterWarning.style.display = 'block';
    }
});

// Auto-calculate jam_selesai based on jam_mulai + 2 hours (typical class duration)
document.getElementById('jam_mulai').addEventListener('change', function() {
    const jamMulai = this.value;
    if (jamMulai) {
        const [hours, minutes] = jamMulai.split(':');
        const endHours = (parseInt(hours) + 2).toString().padStart(2, '0');
        document.getElementById('jam_selesai').value = `${endHours}:${minutes}`;
    }
});

// SweetAlert confirmation for form submission
function confirmSubmit() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Simpan Jadwal?',
            text: 'Apakah Anda yakin ingin menyimpan jadwal kuliah ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            confirmButtonColor: '#0e4a95',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('jadwalCreateForm').submit();
            }
        });
    } else {
        // Fallback if SweetAlert2 not available
        document.getElementById('jadwalCreateForm').submit();
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
@endsection

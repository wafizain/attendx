@extends('layouts.master')

@section('title', 'Detail Mahasiswa')
@section('page-title', 'Detail Mahasiswa')

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }
    
    .detail-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #E5E7EB;
        background: white;
    }
    
    .detail-card-body {
        padding: 1.5rem;
    }
    
    .info-table th {
        width: 200px;
        font-weight: 600;
        color: #374151;
        padding: 0.75rem;
        background: #F9FAFB;
    }
    
    .info-table td {
        padding: 0.75rem;
        color: #111827;
    }
    
    .section-header {
        background: #F3F4F6;
        font-weight: 600;
        color: #111827;
        text-align: center;
    }
    
    .login-info-box {
        background: #FEF3C7;
        border: 1px solid #FCD34D;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .biometric-card {
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .fingerprint-scanner {
        width: 200px;
        height: 250px;
        border: 2px dashed #CBD5E0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F7FAFC;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .fingerprint-scanner:hover {
        border-color: #4299E1;
        background: #EBF8FF;
    }
    
    .fingerprint-scanner.scanning {
        border-color: #48BB78;
        background: #F0FFF4;
        animation: pulse 1.5s infinite;
    }
    
    .face-scanner {
        width: 300px;
        height: 300px;
        border: 2px dashed #CBD5E0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F7FAFC;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    
    .face-scanner:hover {
        border-color: #48BB78;
        background: #F0FFF4;
    }
    
    .face-scanner.scanning {
        border-color: #10B981;
        background: #ECFDF5;
        animation: pulse 1.5s infinite;
    }
    
    .face-scanner video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        -webkit-transform: scaleX(-1);
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endpush

@section('content')
<div class="detail-card">
    <div class="detail-card-header">
        <h5 class="mb-1 fw-semibold">Detail Mahasiswa</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ $mahasiswa->nama }} - {{ $mahasiswa->nim }}</p>
    </div>
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs px-3 pt-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                <i class="fas fa-info-circle me-2"></i>Informasi Umum
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="biometrik-tab" data-bs-toggle="tab" data-bs-target="#biometrik" type="button" role="tab">
                <i class="fas fa-fingerprint me-2"></i>Biometrik
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="face-tab" data-bs-toggle="tab" data-bs-target="#face" type="button" role="tab">
                <i class="fas fa-user-circle me-2"></i>Face
            </button>
        </li>
    </ul>
    
    <div class="detail-card-body">
        <div class="tab-content">
            <!-- Tab Informasi Umum -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
        <table class="table table-bordered info-table">
            <tr>
                <th>NIM</th>
                <td>{{ $mahasiswa->nim ?? '-' }}</td>
            </tr>
            <tr>
                <th>Nama Lengkap</th>
                <td>{{ $mahasiswa->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $mahasiswa->email ?? '-' }}</td>
            </tr>
            <tr>
                <th>Program Studi</th>
                <td>{{ $mahasiswa->prodi->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kelas</th>
                <td>
                    @if($mahasiswa->kelasMembers->whereNull('tanggal_keluar')->first())
                        {{ $mahasiswa->kelasMembers->whereNull('tanggal_keluar')->first()->kelas->nama ?? '-' }}
                    @else
                        {{ $mahasiswa->kelas->nama ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Angkatan</th>
                <td>{{ $mahasiswa->angkatan }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $mahasiswa->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <th>Terdaftar</th>
                <td>{{ $mahasiswa->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            
            @if($mahasiswa->user)
            <tr>
                <th colspan="2" class="section-header">
                    <i class="fas fa-lock me-2"></i>
                    Informasi Akun Login
                </th>
            </tr>
            <tr>
                <th>Username</th>
                <td>
                    <code style="font-size: 1rem;">{{ $mahasiswa->user->username ?? '-' }}</code>
                </td>
            </tr>
            <tr>
                <th>Password Awal</th>
                <td>
                    @if(!empty($mahasiswa->password_plain))
                        <code style="font-size: 1rem; background: #FEF3C7; color: #92400E; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $mahasiswa->password_plain }}</code>
                        <div class="login-info-box mt-2">
                            <i class="fas fa-info-circle text-warning me-2"></i>
                            Password akun dapat dilihat di halaman ini dan bisa direset kapan saja melalui aksi Reset Password.
                        </div>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
            @endif
        </table>
        
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali
            </a>
            <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-warning" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-edit me-2"></i>
                Edit
            </a>
            @if($mahasiswa->user)
            <form action="{{ route('mahasiswa.reset-username', $mahasiswa->id) }}" method="POST" style="display:inline-block;" id="reset-username-form">
                @csrf
                <button type="submit" class="btn btn-primary" style="background-color:#dc3545;border-color:#dc3545;color:#ffffff;" onclick="return confirmResetUsername(event, 'reset-username-form');">
                    <i class="fas fa-user-cog me-2"></i>
                    Reset Username
                </button>
            </form>
            <form action="{{ route('mahasiswa.reset-password', $mahasiswa->id) }}" method="POST" style="display:inline-block;" id="reset-password-form">
                @csrf
                <button type="submit" class="btn btn-info" style="background-color:#dc3545;border-color:#dc3545;color:#ffffff;" onclick="return confirmResetPassword(event, 'reset-password-form');">
                    <i class="fas fa-key me-2"></i>
                    Reset Password
                </button>
            </form>
            @endif
        </div>
            </div>
            <!-- End Tab Informasi Umum -->
            
            <!-- Tab Biometrik -->
            <div class="tab-pane fade" id="biometrik" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="biometric-card">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-fingerprint text-primary me-2"></i>
                                Pendaftaran Sidik Jari
                            </h6>
                            
                            <div class="text-center mb-3">
                                <div class="fingerprint-scanner" id="fpScanner" onclick="startFingerprintScan()">
                                    <div class="text-center">
                                        <i class="fas fa-fingerprint fa-5x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Klik untuk Scan</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" id="fpStatus">
                                <i class="fas fa-info-circle me-2"></i>
                                Status: 
                                @if($mahasiswa->fp_enrolled)
                                    <strong class="text-success">Sudah Terdaftar</strong>
                                    <br><small>Terakhir: {{ $mahasiswa->last_enrolled_at ? $mahasiswa->last_enrolled_at->format('d/m/Y H:i') : '-' }}</small>
                                @else
                                    <strong class="text-warning">Belum Terdaftar</strong>
                                @endif
                            </div>
                            
                            <button type="button" class="btn btn-primary w-100" style="background-color:#0e4a95;border-color:#0e4a95;" onclick="enrollFingerprint()">
                                <i class="fas fa-fingerprint me-2"></i>
                                Daftarkan Sidik Jari
                            </button>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Riwayat Biometrik -->
                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-history text-secondary me-2"></i>
                        Riwayat Pendaftaran Biometrik
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tipe</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Quality Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mahasiswa->biometrik as $index => $bio)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($bio->tipe == 'fingerprint')
                                            <i class="fas fa-fingerprint text-primary me-1"></i> Sidik Jari
                                        @else
                                            <i class="fas fa-user-circle text-success me-1"></i> Wajah
                                        @endif
                                    </td>
                                    <td>{{ $bio->enrolled_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($bio->quality_score)
                                            <span class="badge bg-{{ $bio->quality_score >= 80 ? 'success' : ($bio->quality_score >= 60 ? 'warning' : 'danger') }}">
                                                {{ $bio->quality_score }}%
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($bio->revoked_at)
                                            <span class="badge bg-secondary">Dicabut</span>
                                        @else
                                            <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada riwayat pendaftaran biometrik</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Tab Biometrik -->
            
            <!-- Tab Face -->
            <div class="tab-pane fade" id="face" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="biometric-card">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-user-circle text-success me-2"></i>
                                Pendaftaran Pengenalan Wajah
                            </h6>
                            
                            <div class="text-center mb-3">
                                <div class="face-scanner" id="faceScanner" onclick="startFaceScan()">
                                    <div class="text-center" id="facePlaceholder">
                                        <i class="fas fa-user-circle fa-5x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Klik untuk Scan Wajah</p>
                                    </div>
                                    <video id="faceVideo" style="display:none;" autoplay></video>
                                    <canvas id="faceCanvas" style="display:none;"></canvas>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" id="faceStatus">
                                <i class="fas fa-info-circle me-2"></i>
                                Status: 
                                @if($mahasiswa->face_enrolled ?? false)
                                    <strong class="text-success">Sudah Terdaftar</strong>
                                    <br><small>Terakhir: {{ $mahasiswa->last_enrolled_at ? $mahasiswa->last_enrolled_at->format('d/m/Y H:i') : '-' }}</small>
                                @else
                                    <strong class="text-warning">Belum Terdaftar</strong>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success flex-fill" onclick="enrollFace()">
                                    <i class="fas fa-camera me-2"></i>
                                    Daftarkan Wajah
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="stopFaceScan()">
                                    <i class="fas fa-stop me-2"></i>
                                    Stop
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="biometric-card">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                Panduan
                            </h6>
                            <ul class="small text-muted mb-0">
                                <li>Pastikan wajah terlihat jelas</li>
                                <li>Pencahayaan cukup terang</li>
                                <li>Posisi wajah menghadap kamera</li>
                                <li>Lepas kacamata jika memungkinkan</li>
                                <li>Jangan ada objek menutupi wajah</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Riwayat Face Recognition -->
                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-history text-secondary me-2"></i>
                        Riwayat Pendaftaran Wajah
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Quality Score</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $faceRecords = $mahasiswa->biometrik->where('tipe', 'face');
                                @endphp
                                @forelse($faceRecords as $index => $bio)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bio->enrolled_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($bio->quality_score)
                                            <span class="badge bg-{{ $bio->quality_score >= 80 ? 'success' : ($bio->quality_score >= 60 ? 'warning' : 'danger') }}">
                                                {{ $bio->quality_score }}%
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($bio->revoked_at)
                                            <span class="badge bg-secondary">Dicabut</span>
                                        @else
                                            <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$bio->revoked_at)
                                            <form action="{{ route('biometrik.revoke', $bio->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Yakin ingin mencabut template ini?')">
                                                    <i class="fas fa-ban"></i> Cabut
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('biometrik.destroy', $bio->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus template ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada riwayat pendaftaran wajah</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Tab Face -->
        </div>
    </div>
</div>

@push('scripts')
<script>
let isScanning = false;

function confirmResetUsername(event, formId) {
    event.preventDefault();
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Reset Username?',
            text: 'Username mahasiswa akan direset ke default.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId)?.submit();
            }
        });
    } else {
        if (confirm('Yakin ingin reset username mahasiswa ini?')) {
            document.getElementById(formId)?.submit();
        }
    }
}

function confirmResetPassword(event, formId) {
    event.preventDefault();
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password mahasiswa akan direset ke default.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId)?.submit();
            }
        });
    } else {
        if (confirm('Yakin ingin reset password mahasiswa ini?')) {
            document.getElementById(formId)?.submit();
        }
    }
}

function startFingerprintScan() {
    if (isScanning) return;
    
    const scanner = document.getElementById('fpScanner');
    scanner.classList.add('scanning');
    isScanning = true;
    
    // Simulasi scanning (ganti dengan integrasi hardware sebenarnya)
    setTimeout(() => {
        scanner.classList.remove('scanning');
        isScanning = false;
    }, 3000);
}

function enrollFingerprint() {
    if (!confirm('Daftarkan sidik jari untuk mahasiswa ini?')) return;
    
    // Simulasi enrollment - ganti dengan API call ke hardware
    const data = {
        tipe: 'fingerprint',
        ext_ref: 'FP_' + Date.now(),
        quality_score: Math.floor(Math.random() * 30) + 70 // Random 70-100
    };
    
    fetch('{{ route("mahasiswa.biometrik.enroll", $mahasiswa->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Sidik jari berhasil didaftarkan!');
            location.reload();
        } else {
            alert('Gagal: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

// Face Recognition Functions
let faceStream = null;
let isFaceScanning = false;

function startFaceScan() {
    if (isFaceScanning) return;
    
    const scanner = document.getElementById('faceScanner');
    const video = document.getElementById('faceVideo');
    const placeholder = document.getElementById('facePlaceholder');
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({ video: { width: 300, height: 300 } })
        .then(stream => {
            faceStream = stream;
            video.srcObject = stream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            scanner.classList.add('scanning');
            isFaceScanning = true;
        })
        .catch(error => {
            alert('Tidak dapat mengakses kamera: ' + error.message);
        });
}

function stopFaceScan() {
    if (faceStream) {
        faceStream.getTracks().forEach(track => track.stop());
        faceStream = null;
    }
    
    const scanner = document.getElementById('faceScanner');
    const video = document.getElementById('faceVideo');
    const placeholder = document.getElementById('facePlaceholder');
    
    video.style.display = 'none';
    placeholder.style.display = 'block';
    scanner.classList.remove('scanning');
    isFaceScanning = false;
}

function enrollFace() {
    if (!isFaceScanning) {
        alert('Silakan mulai scan wajah terlebih dahulu!');
        return;
    }
    
    if (!confirm('Daftarkan wajah untuk mahasiswa ini?')) return;
    
    const video = document.getElementById('faceVideo');
    const canvas = document.getElementById('faceCanvas');
    const context = canvas.getContext('2d');
    
    // Capture image from video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    // Convert to base64
    const imageData = canvas.toDataURL('image/jpeg');
    
    // Simulasi enrollment - ganti dengan API call ke face recognition service
    const data = {
        tipe: 'face',
        ext_ref: 'FACE_' + Date.now(),
        quality_score: Math.floor(Math.random() * 30) + 70, // Random 70-100
        face_embedding_path: imageData // In production, send to backend for processing
    };
    
    fetch('{{ route("mahasiswa.biometrik.enroll", $mahasiswa->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Wajah berhasil didaftarkan!');
            stopFaceScan();
            location.reload();
        } else {
            alert('Gagal: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

</script>
@endpush
@endsection

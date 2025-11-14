@extends('layouts.master')

@section('title', 'Detail Dosen')
@section('page-title', 'Detail Dosen')

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
</style>
@endpush

@section('content')
<div class="detail-card">
    <div class="detail-card-header">
        <h5 class="mb-1 fw-semibold">Detail Dosen</h5>
        <p class="text-muted mb-0" style="font-size: 0.875rem;">Informasi lengkap data dosen</p>
    </div>
    
    <div class="detail-card-body">
        <table class="table table-bordered info-table">
            <tr>
                <th>NIDN</th>
                <td>{{ $dosen->nidn ?? '-' }}</td>
            </tr>
            <tr>
                <th>Nama Lengkap</th>
                <td><strong>{{ $dosen->nama ?? $user->name }}</strong></td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $dosen->email ?? $user->email ?? '-' }}</td>
            </tr>
            
            <tr>
                <th>Jabatan Akademik</th>
                <td>{{ $dosen->jabatan_akademik ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($user->status == 'aktif')
                        Aktif
                    @else
                        Nonaktif
                    @endif
                </td>
            </tr>
            <tr>
                <th>Terdaftar</th>
                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            
            <tr>
                <th colspan="2" class="section-header">
                    <i class="fas fa-lock me-2"></i>
                    Informasi Akun Login
                </th>
            </tr>
            <tr>
                <th>Username</th>
                <td>
                    <code style="font-size: 1rem;">{{ $user->username ?? '-' }}</code>
                </td>
            </tr>
            <tr>
                <th>Password Awal</th>
                <td>
                    @if(!empty($dosen?->password_plain))
                        <code style="font-size: 1rem; background: #FEF3C7; color: #92400E; padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $dosen->password_plain }}</code>
                        <div class="login-info-box mt-2">
                            <i class="fas fa-info-circle text-warning me-2"></i>
                            Password akun dapat dilihat di halaman ini dan bisa direset kapan saja melalui aksi Reset Password.
                        </div>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        </table>
        
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali
            </a>
            <a href="{{ route('admin.dosen.edit', $user->id) }}" class="btn btn-warning" style="background-color:#0e4a95;border-color:#0e4a95;">
                <i class="fas fa-edit me-2"></i>
                Edit
            </a>
            <form action="{{ route('admin.dosen.reset-username', $user->id) }}" method="POST" style="display:inline-block;" id="resetUsernameForm">
                @csrf
                <button type="submit" class="btn btn-primary" style="background-color:#dc3545;border-color:#dc3545;color:#ffffff;" onclick="event.preventDefault(); confirmResetUsername();">
                    <i class="fas fa-user-cog me-2"></i>
                    Reset Username
                </button>
            </form>
            <form action="{{ route('admin.dosen.reset-password', $user->id) }}" method="POST" style="display:inline-block;" id="resetPasswordForm">
                @csrf
                <button type="submit" class="btn btn-info" style="background-color:#dc3545;border-color:#dc3545;color:#ffffff;" onclick="event.preventDefault(); confirmResetPassword();">
                    <i class="fas fa-key me-2"></i>
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmResetUsername() {
        Swal.fire({
            title: 'Reset Username?',
            text: 'Username dosen akan di-reset ke format default.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetUsernameForm').submit();
            }
        });
    }

    function confirmResetPassword() {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password dosen akan di-reset dan password baru akan ditampilkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetPasswordForm').submit();
            }
        });
    }
</script>
@endpush

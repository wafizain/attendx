@extends('layouts.master')

@section('title', 'Detail Ruangan')
@section('page-title', 'Detail Ruangan')

@push('styles')
<style>
    .info-table th {
        width: 200px;
        font-weight: 600;
        background: #F9FAFB;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Informasi Ruangan -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informasi Ruangan</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>Kode Ruangan</th>
                        <td>{{ $ruangan->kode }}</td>
                    </tr>
                    <tr>
                        <th>Nama Ruangan</th>
                        <td>{{ $ruangan->nama }}</td>
                    </tr>
                    <tr>
                        <th>Kapasitas</th>
                        <td>{{ $ruangan->kapasitas }} Orang</td>
                    </tr>
                    <tr>
                        <th>Lokasi/Gedung</th>
                        <td>{{ $ruangan->lokasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($ruangan->status == 'aktif')
                                Aktif
                            @else
                                Nonaktif
                            @endif
                        </td>
                    </tr>
                    @if($ruangan->keterangan)
                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $ruangan->keterangan }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Dibuat Pada</th>
                        <td>{{ $ruangan->created_at->format('d F Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir Diupdate</th>
                        <td>{{ $ruangan->updated_at->format('d F Y H:i') }}</td>
                    </tr>
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('ruangan.index') }}" class="btn btn-secondary" style="background-color:#6C757D;border-color:#6C757D;">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <a href="{{ route('ruangan.edit', $ruangan->id) }}" class="btn btn-warning" style="background-color:#0e4a95;border-color:#0e4a95;">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <form action="{{ route('ruangan.toggle-status', $ruangan->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-{{ $ruangan->status == 'aktif' ? 'secondary' : 'success' }}" style="background-color:{{ $ruangan->status == 'aktif' ? '#6C757D' : '#28a745' }};border-color:{{ $ruangan->status == 'aktif' ? '#6C757D' : '#28a745' }};">
                            <i class="fas fa-{{ $ruangan->status == 'aktif' ? 'ban' : 'check' }} me-2"></i>
                            {{ $ruangan->status == 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $ruangan->id }}, '{{ $ruangan->nama_ruangan }}')" style="background-color:#dc3545;border-color:#dc3545;color:#ffffff;">
                        <i class="fas fa-trash me-2"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Statistik -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Statistik Penggunaan</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Jadwal</small>
                    <h3 class="mb-0">{{ $stats['total_jadwal'] }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Jadwal Aktif</small>
                    <h3 class="mb-0">{{ $stats['jadwal_aktif'] }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Utilisasi Ruangan</small>
                    <h3 class="mb-0">{{ $stats['utilisasi'] }}%</h3>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-{{ $stats['utilisasi'] > 75 ? 'danger' : ($stats['utilisasi'] > 50 ? 'warning' : 'success') }}" 
                             role="progressbar" style="width: {{ $stats['utilisasi'] }}%"></div>
                    </div>
                    <small class="text-muted">Berdasarkan jadwal aktif (Senin-Jumat, 08:00-17:00)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Kuliah Aktif -->
<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">Jadwal Kuliah Aktif ({{ $jadwalAktif->count() }})</h5>
    </div>
    <div class="card-body">
        @if($jadwalAktif->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Paralel</th>
                            <th>Peserta</th>
                            <th width="80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadwalAktif as $index => $jadwal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $jadwal->mataKuliah->kode_mk }}</strong><br>
                                <small>{{ $jadwal->mataKuliah->nama_mk }}</small>
                            </td>
                            <td><small>{{ $jadwal->dosen->name }}</small></td>
                            <td>
                                {{ $jadwal->hari_nama }}
                            </td>
                            <td>
                                <small>
                                    {{ date('H:i', strtotime($jadwal->jam_mulai)) }} - 
                                    {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                                </small>
                            </td>
                            <td class="text-center">
                                @if($jadwal->paralel)
                                    {{ $jadwal->paralel }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $jadwal->mahasiswa->count() }}
                            </td>
                            <td>
                                <a href="{{ route('jadwal.show', $jadwal->id) }}" class="btn btn-sm btn-info" title="Detail Jadwal">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Belum ada jadwal kuliah aktif yang menggunakan ruangan ini.
            </div>
        @endif
    </div>
</div>

<!-- Delete Form -->
@push('scripts')
<script>
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Ruangan?',
        text: 'Yakin ingin menghapus ruangan "' + nama + '"? Ruangan yang masih digunakan dalam jadwal aktif tidak dapat dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("ruangan.destroy", ":id") }}'.replace(':id', id);
            
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection

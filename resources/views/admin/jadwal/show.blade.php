@extends('layouts.master')

@section('title', 'Detail Jadwal Kuliah')
@section('page-title', 'Detail Jadwal Kuliah')

@push('styles')
<style>
    .info-table th {
        width: 200px;
        font-weight: 600;
        background: #F9FAFB;
    }
    .section-header {
        background: #F3F4F6;
        font-weight: 600;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Informasi Jadwal -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informasi Jadwal</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>Mata Kuliah</th>
                        <td>
                            <strong>{{ $jadwal->mataKuliah->kode_mk }}</strong> - {{ $jadwal->mataKuliah->nama_mk }}<br>
                            <small class="text-muted">{{ $jadwal->mataKuliah->sks }} SKS</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Dosen Pengampu</th>
                        <td>{{ $jadwal->dosen->name }}</td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td>
                            @if($jadwal->kelas)
                                <strong>{{ $jadwal->kelas->kode }}</strong> - {{ $jadwal->kelas->nama }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2" class="section-header">
                            <i class="fas fa-clock me-2"></i>Waktu & Ruangan
                        </th>
                    </tr>
                    <tr>
                        <th>Hari</th>
                        <td><span class="badge bg-info">{{ $jadwal->hari_nama }}</span></td>
                    </tr>
                    <tr>
                        <th>Jam</th>
                        <td>
                            <strong>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Ruangan</th>
                        <td>
                            <span class="badge bg-secondary">{{ $jadwal->ruangan->kode }}</span> - {{ $jadwal->ruangan->nama }}<br>
                            <small class="text-muted">Kapasitas: {{ $jadwal->ruangan->kapasitas }} | Lokasi: {{ $jadwal->ruangan->lokasi }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2" class="section-header">
                            <i class="fas fa-calendar me-2"></i>Periode
                        </th>
                    </tr>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <td>{{ $jadwal->tanggal_mulai->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Selesai</th>
                        <td>{{ $jadwal->tanggal_selesai->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Pertemuan</th>
                        <td><span class="badge bg-primary">{{ $jadwal->pertemuan->count() }} Pertemuan</span></td>
                    </tr>
                    <tr>
                        <th colspan="2" class="section-header">
                            <i class="fas fa-cog me-2"></i>Aturan Absensi
                        </th>
                    </tr>
                    <tr>
                        <th>Buka Absensi</th>
                        <td>{{ $jadwal->absen_open_min }} menit sebelum jam mulai</td>
                    </tr>
                    <tr>
                        <th>Toleransi Telat</th>
                        <td>{{ $jadwal->grace_late_min }} menit dari jam mulai</td>
                    </tr>
                    <tr>
                        <th>Tutup Absensi</th>
                        <td>{{ $jadwal->absen_close_min }} menit setelah jam mulai</td>
                    </tr>
                    <tr>
                        <th>Wajib Foto Wajah</th>
                        <td>
                            @if($jadwal->wajah_wajib)
                                <span class="badge bg-success">Ya</span>
                            @else
                                <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($jadwal->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @if($jadwal->catatan)
                    <tr>
                        <th>Catatan</th>
                        <td>{{ $jadwal->catatan }}</td>
                    </tr>
                    @endif
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <a href="{{ route('jadwal.edit', $jadwal->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
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
                <h6 class="mb-0">Statistik</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Peserta</small>
                    <h3 class="mb-0">{{ $jadwal->mahasiswa->count() }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Total Pertemuan</small>
                    <h3 class="mb-0">{{ $jadwal->pertemuan->count() }}</h3>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Pertemuan Selesai</small>
                    <h3 class="mb-0">{{ $jadwal->pertemuan->where('status_sesi', 'selesai')->count() }}</h3>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class="fas fa-user-plus me-2"></i>Tambah Peserta
                </button>
                <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#enrollKelasModal">
                    <i class="fas fa-users me-2"></i>Tambah dari Kelas
                </button>
                <a href="#pertemuan-section" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-calendar-alt me-2"></i>Lihat Pertemuan
                </a>
                <a href="#peserta-section" class="btn btn-success w-100">
                    <i class="fas fa-users me-2"></i>Lihat Peserta
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Enroll Kelas -->
<div class="modal fade" id="enrollKelasModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('jadwal.enroll-kelas', $jadwal->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Peserta dari Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kls)
                                <option value="{{ $kls->id }}">{{ $kls->kode }} - {{ $kls->nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Semua anggota aktif kelas akan ditambahkan sebagai peserta.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
    </div>

<!-- Daftar Peserta -->
<div class="card mt-3" id="peserta-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Peserta ({{ $jadwal->mahasiswa->count() }})</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollModal">
            <i class="fas fa-user-plus me-2"></i>Tambah Peserta
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#enrollKelasModal">
            <i class="fas fa-users me-2"></i>Tambah dari Kelas
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>Tanggal Daftar</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal->mahasiswa as $index => $mhs)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><code>{{ $mhs->nim }}</code></td>
                        <td>{{ $mhs->nama }}</td>
                        <td><small>{{ $mhs->prodi->nama ?? '-' }}</small></td>
                        <td>
                            @php $tglDaftar = $mhs->pivot->tanggal_daftar ?? null; @endphp
                            <small>{{ $tglDaftar ? \Carbon\Carbon::parse($tglDaftar)->format('d/m/Y') : '-' }}</small>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmRemoveMahasiswa({{ $mhs->id }}, '{{ $mhs->nama }}')">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada peserta terdaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daftar Pertemuan -->
<div class="card mt-3" id="pertemuan-section">
    <div class="card-header">
        <h5 class="mb-0">Daftar Pertemuan ({{ $jadwal->pertemuan->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="50">Minggu</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Materi</th>
                        <th>Status</th>
                        <th>Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwal->pertemuan as $pertemuan)
                    <tr>
                        <td class="text-center">{{ $pertemuan->minggu_ke }}</td>
                        <td>{{ $pertemuan->tanggal->format('d/m/Y') }}</td>
                        <td><small>{{ date('H:i', strtotime($pertemuan->jam_mulai)) }} - {{ date('H:i', strtotime($pertemuan->jam_selesai)) }}</small></td>
                        <td><span class="badge bg-secondary">{{ $pertemuan->ruangan->kode }}</span></td>
                        <td><small>{{ $pertemuan->materi ?? '-' }}</small></td>
                        <td>
                            @if($pertemuan->status_sesi == 'direncanakan')
                                <span class="badge bg-secondary">Direncanakan</span>
                            @elseif($pertemuan->status_sesi == 'berjalan')
                                <span class="badge bg-primary">Berjalan</span>
                            @elseif($pertemuan->status_sesi == 'selesai')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-danger">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($pertemuan->status_sesi == 'selesai')
                                <span class="badge bg-info">{{ $pertemuan->absensi->count() }}/{{ $jadwal->mahasiswa->count() }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Enroll Mahasiswa -->
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('jadwal.enroll', $jadwal->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Peserta Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Mahasiswa</label>
                        <select name="mahasiswa_ids[]" class="form-select" multiple size="10" required>
                            @foreach(\App\Models\Mahasiswa::aktif()->orderBy('nama')->get() as $mhs)
                                @if(!$jadwal->mahasiswa->contains($mhs->id))
                                    <option value="{{ $mhs->id }}">{{ $mhs->nim }} - {{ $mhs->nama }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Tahan Ctrl/Cmd untuk memilih multiple mahasiswa</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" action="{{ route('jadwal.destroy', $jadwal->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Remove Mahasiswa Form -->
<form id="remove-mahasiswa-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Yakin ingin menghapus jadwal ini? Semua pertemuan dan absensi terkait akan ikut terhapus.')) {
        document.getElementById('delete-form').submit();
    }
}

function confirmRemoveMahasiswa(mahasiswaId, nama) {
    if (confirm(`Yakin ingin mengeluarkan ${nama} dari jadwal ini?`)) {
        const form = document.getElementById('remove-mahasiswa-form');
        form.action = `/jadwal/{{ $jadwal->id }}/mahasiswa/${mahasiswaId}`;
        form.submit();
    }
}
</script>
@endpush
@endsection

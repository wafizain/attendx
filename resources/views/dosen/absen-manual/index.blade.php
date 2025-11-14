@extends('layouts.master')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Absen Manual</h2>
            <p class="text-muted">Kelola absensi mahasiswa secara manual jika sidik jari bermasalah</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Pilih Kelas</label>
                            <select class="form-select" id="kelasSelect">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">
                                    {{ $kelas->nama_kelas }} - {{ $kelas->mataKuliah->nama_matkul }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pilih Sesi Absensi</label>
                            <select class="form-select" id="sesiSelect" disabled>
                                <option value="">-- Pilih Sesi --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mahasiswa List -->
                    <div id="mahasiswaContainer" style="display: none;">
                        <h5 class="mb-3">Daftar Mahasiswa</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Status</th>
                                        <th>Waktu Absen</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="mahasiswaList">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Pilih kelas dan sesi untuk mulai absen manual</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Absensi -->
<div class="modal fade" id="editAbsensiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editMahasiswaId">
                <div class="mb-3">
                    <label class="form-label">Nama Mahasiswa</label>
                    <input type="text" class="form-control" id="editMahasiswaName" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Absensi</label>
                    <select class="form-select" id="editStatus">
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea class="form-control" id="editKeterangan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveAbsensi()">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentSesiId = null;

// Load sesi when kelas selected
$('#kelasSelect').change(function() {
    const kelasId = $(this).val();
    $('#sesiSelect').prop('disabled', true).html('<option value="">-- Pilih Sesi --</option>');
    $('#mahasiswaContainer').hide();
    $('#emptyState').show();
    
    if (kelasId) {
        $.ajax({
            url: '{{ route("dosen.absen-manual.get-sesi") }}',
            method: 'GET',
            data: { kelas_id: kelasId },
            success: function(response) {
                let options = '<option value="">-- Pilih Sesi --</option>';
                response.forEach(function(sesi) {
                    const tanggal = new Date(sesi.tanggal).toLocaleDateString('id-ID');
                    options += `<option value="${sesi.id}">
                        ${tanggal} - ${sesi.topik || 'Pertemuan ' + (sesi.pertemuan_ke || '-')}
                    </option>`;
                });
                $('#sesiSelect').html(options).prop('disabled', false);
            }
        });
    }
});

// Load mahasiswa when sesi selected
$('#sesiSelect').change(function() {
    const sesiId = $(this).val();
    currentSesiId = sesiId;
    
    if (sesiId) {
        $('#loadingIndicator').show();
        $('#emptyState').hide();
        $('#mahasiswaContainer').hide();
        
        $.ajax({
            url: '{{ route("dosen.absen-manual.get-mahasiswa") }}',
            method: 'GET',
            data: { sesi_id: sesiId },
            success: function(response) {
                let html = '';
                response.forEach(function(mhs, index) {
                    const statusBadge = getStatusBadge(mhs.status);
                    const waktuAbsen = mhs.waktu_absen ? new Date(mhs.waktu_absen).toLocaleTimeString('id-ID') : '-';
                    
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${mhs.no_induk}</td>
                            <td>${mhs.name}</td>
                            <td>${statusBadge}</td>
                            <td>${waktuAbsen}</td>
                            <td>${mhs.keterangan || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editAbsensi(${mhs.id}, '${mhs.name}', '${mhs.status}', '${mhs.keterangan || ''}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                $('#mahasiswaList').html(html);
                $('#loadingIndicator').hide();
                $('#mahasiswaContainer').show();
            }
        });
    } else {
        $('#mahasiswaContainer').hide();
        $('#emptyState').show();
    }
});

function getStatusBadge(status) {
    const badges = {
        'hadir': '<span class="badge bg-success">Hadir</span>',
        'izin': '<span class="badge bg-info">Izin</span>',
        'sakit': '<span class="badge bg-warning">Sakit</span>',
        'alpha': '<span class="badge bg-danger">Alpha</span>'
    };
    return badges[status] || badges['alpha'];
}

function editAbsensi(mahasiswaId, name, status, keterangan) {
    $('#editMahasiswaId').val(mahasiswaId);
    $('#editMahasiswaName').val(name);
    $('#editStatus').val(status);
    $('#editKeterangan').val(keterangan);
    $('#editAbsensiModal').modal('show');
}

function saveAbsensi() {
    const mahasiswaId = $('#editMahasiswaId').val();
    const status = $('#editStatus').val();
    const keterangan = $('#editKeterangan').val();
    
    $.ajax({
        url: '{{ route("dosen.absen-manual.update") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            sesi_id: currentSesiId,
            mahasiswa_id: mahasiswaId,
            status: status,
            keterangan: keterangan
        },
        success: function(response) {
            $('#editAbsensiModal').modal('hide');
            // Reload mahasiswa list
            $('#sesiSelect').trigger('change');
            
            // Show success message
            alert('Absensi berhasil diupdate!');
        },
        error: function(xhr) {
            alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
        }
    });
}
</script>
@endsection

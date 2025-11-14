@extends('layouts.master')

@section('title', 'Arsip Data')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-archive me-2"></i>
                        Arsip Data
                    </h4>
                    <p class="text-muted mb-0">Data yang telah dihapus dan dapat dikembalikan atau dihapus permanen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Type Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ $type == 'mahasiswa' ? 'active' : '' }}" 
                               href="{{ route('arsip.index', ['type' => 'mahasiswa']) }}">
                                <i class="fas fa-user-graduate me-2"></i>
                                Mahasiswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $type == 'dosen' ? 'active' : '' }}" 
                               href="{{ route('arsip.index', ['type' => 'dosen']) }}">
                                <i class="fas fa-chalkboard-teacher me-2"></i>
                                Dosen
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('arsip.index') }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ $search }}"
                                           placeholder="Cari nama, NIM/NIP, atau username...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>
                                    Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($data->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                <label for="selectAll" class="form-check-label me-3">Pilih Semua</label>
                                
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-success btn-sm" 
                                            onclick="bulkAction('restore')"
                                            disabled>
                                        <i class="fas fa-undo me-1"></i>
                                        Restore
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm" 
                                            onclick="bulkAction('permanent_delete')"
                                            disabled>
                                        <i class="fas fa-trash me-1"></i>
                                        Hapus Permanen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input bulk-checkbox">
                                        </th>
                                        @if($type == 'mahasiswa')
                                            <th>NIM</th>
                                            <th>Nama</th>
                                            <th>Program Studi</th>
                                            <th>Kelas</th>
                                            <th>Status</th>
                                        @elseif($type == 'dosen')
                                            <th>Nama</th>
                                            <th>NIP</th>
                                            <th>Program Studi</th>
                                        @endif
                                        <th>Diarsipkan Pada</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       class="form-check-input item-checkbox" 
                                                       value="{{ $item->id }}">
                                            </td>
                                            @if($type == 'mahasiswa')
                                                <td><code>{{ $item->nim }}</code></td>
                                                <td>{{ $item->nama }}</td>
                                                <td>{{ $item->prodi->nama ?? '-' }}</td>
                                                <td>{{ $item->kelas->nama ?? '-' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $item->status_akademik == 'aktif' ? 'success' : ($item->status_akademik == 'cuti' ? 'warning' : ($item->status_akademik == 'nonaktif' ? 'danger' : 'secondary')) }}">
                                                        {{ ucfirst($item->status_akademik) }}
                                                    </span>
                                                </td>
                                            @elseif($type == 'dosen')
                                                <td>{{ $item->nama }}</td>
                                                <td><code>{{ $item->nip }}</code></td>
                                                <td>{{ $item->prodi->nama ?? '-' }}</td>
                                            @endif
                                            <td>
                                                <small class="text-muted">
                                                    {{ $item->deleted_at->format('d M Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-success" 
                                                            onclick="restoreItem('{{ $type }}', {{ $item->id }})"
                                                            title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger" 
                                                            onclick="permanentDelete('{{ $type }}', {{ $item->id }})"
                                                            title="Hapus Permanen">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
                            </div>
                            {{ $data->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data di arsip</h5>
                            <p class="text-muted">Belum ada data yang dihapus untuk tipe {{ $type }} ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('arsip.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="action" id="bulkAction">
    <input type="hidden" name="ids" id="bulkIds">
</form>
@endsection

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkButtons();
});

// Individual checkbox change
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
});

function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkButtons = document.querySelectorAll('[onclick^="bulkAction"]');
    
    bulkButtons.forEach(button => {
        button.disabled = checkedBoxes.length === 0;
    });
}

function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Pilih minimal satu data untuk melakukan aksi ini.'
        });
        return;
    }
    
    const confirmText = action === 'restore' 
        ? `Apakah Anda yakin ingin mengembalikan ${ids.length} data dari arsip?`
        : `Apakah Anda yakin ingin menghapus permanen ${ids.length} data? Tindakan ini tidak dapat dibatalkan!`;
    
    const confirmTitle = action === 'restore' ? 'Restore Data' : 'Hapus Permanen';
    const confirmIcon = action === 'restore' ? 'question' : 'warning';
    const confirmButton = action === 'restore' ? 'Ya, Restore' : 'Ya, Hapus Permanen';
    
    Swal.fire({
        title: confirmTitle,
        text: confirmText,
        icon: confirmIcon,
        showCancelButton: true,
        confirmButtonColor: action === 'restore' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmButton,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('bulkAction').value = action;
            document.getElementById('bulkIds').value = ids.join(',');
            document.getElementById('bulkActionForm').submit();
        }
    });
}

function restoreItem(type, id) {
    Swal.fire({
        title: 'Restore Data',
        text: 'Apakah Anda yakin ingin mengembalikan data ini dari arsip?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Restore',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/arsip/${type}/${id}/restore`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function permanentDelete(type, id) {
    Swal.fire({
        title: 'Hapus Permanen',
        text: 'Apakah Anda yakin ingin menghapus permanen data ini? Tindakan ini tidak dapat dibatalkan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Permanen',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/arsip/${type}/${id}/permanent-delete`;
            
            // Add DELETE method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            form.appendChild(methodInput);
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush

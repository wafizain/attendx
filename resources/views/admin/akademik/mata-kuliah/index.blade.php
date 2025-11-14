@extends('layouts.master')

@section('title','Data Mata Kuliah')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
  .card-modern .card-header { background:#fff; border-bottom: 1px solid #eef1f5; }
  .table-modern thead th { background:#f9fafb; color:#111827; font-weight:600; }
  .badge-sks { background: #3b82f6; color: white; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Data Mata Kuliah</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Mata Kuliah</li>
      </ol>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    @endif

    <div class="card card-modern">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Mata Kuliah</h3>
        <a href="{{ route('mata-kuliah.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus mr-1"></i> Tambah Mata Kuliah
        </a>
      </div>

      <div class="card-body">
        <table id="matakuliah-table" class="table table-modern table-hover">
          <thead>
            <tr>
              <th>Kode MK</th>
              <th>Nama Mata Kuliah</th>
              <th>SKS</th>
              <th>Semester</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($mataKuliah as $mk)
            <tr>
              <td class="text-monospace"><strong>{{ $mk->kode_mk }}</strong></td>
              <td>{{ $mk->nama_mk }}</td>
              <td><span class="badge badge-sks">{{ $mk->sks }} SKS</span></td>
              <td>{{ $mk->semester ?? '-' }}</td>
              <td>
                @if($mk->status == 'aktif')
                  <span class="badge badge-success">Aktif</span>
                @else
                  <span class="badge badge-secondary">Nonaktif</span>
                @endif
              </td>
              <td>
                <a href="{{ route('mata-kuliah.show', $mk->id) }}" class="btn btn-info btn-xs" title="Detail">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('mata-kuliah.edit', $mk->id) }}" class="btn btn-warning btn-xs" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#deleteModal{{ $mk->id }}" title="Hapus">
                  <i class="fas fa-trash"></i>
                </button>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal{{ $mk->id }}" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        Yakin ingin menghapus mata kuliah <strong>{{ $mk->nama_mk }}</strong>?
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route('mata-kuliah.destroy', $mk->id) }}" method="POST" style="display:inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($mataKuliah->hasPages())
      <div class="card-footer">
        {{ $mataKuliah->links() }}
      </div>
      @endif
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('AdminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
  $(function () {
    $('#matakuliah-table').DataTable({
      responsive: true,
      autoWidth: false,
      language: {
        search: "Cari:",
        lengthMenu: "Tampil _MENU_ data",
        info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
        paginate: { next:"Berikutnya", previous:"Sebelumnya" }
      }
    });
  });
</script>
@endpush

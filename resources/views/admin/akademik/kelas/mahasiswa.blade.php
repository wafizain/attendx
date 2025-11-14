@extends('layouts.master')

@section('title','Kelola Mahasiswa Kelas')

@push('styles')
<link rel="stylesheet" href="{{ asset('AdminLTE/plugins/select2/css/select2.min.css') }}">
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Kelola Mahasiswa: {{ $kelas->nama_kelas }}</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kelas.index') }}">Kelas</a></li>
        <li class="breadcrumb-item active">Mahasiswa</li>
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

    <!-- Info Kelas -->
    <div class="card card-modern mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <h5 class="mb-2"><strong>{{ $kelas->nama_kelas }}</strong></h5>
            <p class="mb-0">
              <span class="text-monospace text-primary">{{ $kelas->mataKuliah->kode_mk }}</span> - 
              {{ $kelas->mataKuliah->nama_mk }}<br>
              <small class="text-muted">Dosen: {{ $kelas->dosen->name }}</small>
            </p>
          </div>
          <div class="col-md-4 text-right">
            <h3 class="mb-0">{{ $kelas->mahasiswa->count() }} / {{ $kelas->kapasitas }}</h3>
            <small class="text-muted">Mahasiswa Terdaftar</small>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Daftar Mahasiswa Terdaftar -->
      <div class="col-md-7">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Mahasiswa Terdaftar ({{ $kelas->mahasiswa->count() }})</h3>
          </div>
          <div class="card-body">
            @if($kelas->mahasiswa->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th width="5%">No</th>
                      <th>Nama</th>
                      <th>Email</th>
                      <th>Bergabung</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($kelas->mahasiswa as $index => $mhs)
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $mhs->name }}</strong></td>
                        <td><small>{{ $mhs->email }}</small></td>
                        <td><small>{{ $mhs->pivot->tanggal_bergabung->format('d/m/Y') }}</small></td>
                        <td>
                          <button type="button" class="btn btn-danger btn-xs" 
                                  data-toggle="modal" data-target="#removeModal{{ $mhs->id }}">
                            <i class="fas fa-times"></i>
                          </button>

                          <!-- Remove Modal -->
                          <div class="modal fade" id="removeModal{{ $mhs->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title">Konfirmasi</h5>
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                  Hapus <strong>{{ $mhs->name }}</strong> dari kelas?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                  <form action="{{ route('kelas.remove-mahasiswa', [$kelas->id, $mhs->id]) }}" 
                                        method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
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
            @else
              <p class="text-center text-muted py-4">Belum ada mahasiswa terdaftar</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Form Tambah Mahasiswa -->
      <div class="col-md-5">
        <div class="card card-modern">
          <div class="card-header bg-primary">
            <h3 class="card-title mb-0 text-white">Tambah Mahasiswa</h3>
          </div>
          <form action="{{ route('kelas.add-mahasiswa', $kelas->id) }}" method="POST">
            @csrf
            <div class="card-body">
              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              @if($availableMahasiswa->count() > 0)
                <div class="form-group">
                  <label for="mahasiswa_id">Pilih Mahasiswa</label>
                  <select name="mahasiswa_id[]" id="mahasiswa_id" class="form-control select2" 
                          multiple="multiple" required>
                    @foreach($availableMahasiswa as $mhs)
                      <option value="{{ $mhs->id }}">{{ $mhs->name }} ({{ $mhs->email }})</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Pilih satu atau lebih mahasiswa</small>
                </div>

                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i>
                  <small>
                    Tersedia <strong>{{ $availableMahasiswa->count() }}</strong> mahasiswa<br>
                    Sisa kapasitas: <strong>{{ $kelas->kapasitas - $kelas->mahasiswa->count() }}</strong>
                  </small>
                </div>
              @else
                <div class="alert alert-warning">
                  <i class="fas fa-exclamation-triangle"></i>
                  Tidak ada mahasiswa yang tersedia untuk ditambahkan.
                </div>
              @endif
            </div>

            @if($availableMahasiswa->count() > 0)
              <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-block">
                  <i class="fas fa-plus mr-1"></i> Tambah Mahasiswa
                </button>
              </div>
            @endif
          </form>
        </div>

        <div class="card card-modern">
          <div class="card-body">
            <a href="{{ route('kelas.show', $kelas->id) }}" class="btn btn-secondary btn-block">
              <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail Kelas
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('AdminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
  $(function () {
    $('.select2').select2({
      theme: 'bootstrap4',
      width: '100%',
      placeholder: 'Pilih mahasiswa...'
    });
  });
</script>
@endpush

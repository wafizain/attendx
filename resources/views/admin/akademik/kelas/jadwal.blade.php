@extends('layouts.master')

@section('title','Kelola Jadwal Kelas')

@push('styles')
<style>
  .card-modern { border: 1px solid #eef1f5; border-radius: 14px; }
</style>
@endpush

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4 mb-0">Kelola Jadwal: {{ $kelas->nama_kelas }}</h1>
      <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kelas.index') }}">Kelas</a></li>
        <li class="breadcrumb-item active">Jadwal</li>
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
        <h5 class="mb-2"><strong>{{ $kelas->nama_kelas }}</strong></h5>
        <p class="mb-0">
          <span class="text-monospace text-primary">{{ $kelas->mataKuliah->kode_mk }}</span> - 
          {{ $kelas->mataKuliah->nama_mk }}<br>
          <small class="text-muted">Dosen: {{ $kelas->dosen->name }}</small>
        </p>
      </div>
    </div>

    <div class="row">
      <!-- Daftar Jadwal -->
      <div class="col-md-7">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title mb-0">Jadwal Kelas ({{ $kelas->jadwal->count() }})</h3>
          </div>
          <div class="card-body">
            @if($kelas->jadwal->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Hari</th>
                      <th>Jam</th>
                      <th>Durasi</th>
                      <th>Ruangan</th>
                      <th>Status</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($kelas->jadwal->sortBy(function($j) {
                      $days = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
                      return $days[$j->hari] ?? 99;
                    }) as $jadwal)
                      <tr>
                        <td><strong>{{ $jadwal->hari }}</strong></td>
                        <td>
                          {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                          {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                        </td>
                        <td>
                          <span class="badge badge-info">{{ $jadwal->durasi_menit }} menit</span>
                        </td>
                        <td>{{ $jadwal->ruangan ?? '-' }}</td>
                        <td>
                          @if($jadwal->status == 'aktif')
                            <span class="badge badge-success">Aktif</span>
                          @else
                            <span class="badge badge-secondary">Nonaktif</span>
                          @endif
                        </td>
                        <td>
                          <button type="button" class="btn btn-danger btn-xs" 
                                  data-toggle="modal" data-target="#deleteModal{{ $jadwal->id }}">
                            <i class="fas fa-trash"></i>
                          </button>

                          <!-- Delete Modal -->
                          <div class="modal fade" id="deleteModal{{ $jadwal->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title">Konfirmasi</h5>
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                  Hapus jadwal <strong>{{ $jadwal->hari }}</strong>?
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                  <form action="{{ route('kelas.delete-jadwal', [$kelas->id, $jadwal->id]) }}" 
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
              <p class="text-center text-muted py-4">Belum ada jadwal</p>
            @endif
          </div>
        </div>
      </div>

      <!-- Form Tambah Jadwal -->
      <div class="col-md-5">
        <div class="card card-modern">
          <div class="card-header bg-primary">
            <h3 class="card-title mb-0 text-white">Tambah Jadwal</h3>
          </div>
          <form action="{{ route('kelas.store-jadwal', $kelas->id) }}" method="POST">
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

              <div class="form-group">
                <label for="hari">Hari <span class="text-danger">*</span></label>
                <select name="hari" id="hari" class="form-control" required>
                  <option value="">Pilih Hari</option>
                  <option value="Senin" {{ old('hari') == 'Senin' ? 'selected' : '' }}>Senin</option>
                  <option value="Selasa" {{ old('hari') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                  <option value="Rabu" {{ old('hari') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                  <option value="Kamis" {{ old('hari') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                  <option value="Jumat" {{ old('hari') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                  <option value="Sabtu" {{ old('hari') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                  <option value="Minggu" {{ old('hari') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="jam_mulai">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" 
                           value="{{ old('jam_mulai') }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="jam_selesai">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" 
                           value="{{ old('jam_selesai') }}" required>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="ruangan">Ruangan</label>
                <input type="text" name="ruangan" id="ruangan" class="form-control" 
                       placeholder="Contoh: Lab 101" value="{{ old('ruangan', $kelas->ruangan) }}">
                <small class="text-muted">Opsional, default dari kelas</small>
              </div>

              <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_aktif" 
                         value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_aktif">Aktif</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_nonaktif" 
                         value="nonaktif" {{ old('status') == 'nonaktif' ? 'checked' : '' }}>
                  <label class="form-check-label" for="status_nonaktif">Nonaktif</label>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-plus mr-1"></i> Tambah Jadwal
              </button>
            </div>
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

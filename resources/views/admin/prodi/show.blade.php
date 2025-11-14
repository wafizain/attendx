@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Program Studi</h1>
        <div>
            <a href="{{ route('prodi.edit', $prodi->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('prodi.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Info Prodi -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Program Studi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Kode</th>
                            <td><strong class="text-primary">{{ $prodi->kode }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><strong>{{ $prodi->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th>Jenjang</th>
                            <td>{{ $prodi->jenjang }}</td>
                        </tr>
                        <tr>
                            <th>Akreditasi</th>
                            <td>{{ $prodi->akreditasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $prodi->status_label }}</td>
                        </tr>
                        <tr>
                            <th>Kaprodi</th>
                            <td>
                                @if($prodi->kaprodi)
                                    <strong>{{ $prodi->kaprodi->name }}</strong><br>
                                    <small class="text-muted">{{ $prodi->kaprodi->no_induk }}</small>
                                @else
                                    <span class="text-muted">Belum ditentukan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $prodi->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Slug</th>
                            <td><code>{{ $prodi->slug }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistik</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-graduate text-primary"></i> Mahasiswa</span>
                            <strong class="text-primary">{{ $statistik['total_mahasiswa'] }}</strong>
                        </div>
                        <small class="text-muted">Aktif: {{ $statistik['mahasiswa_aktif'] }}</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-door-open text-info"></i> Kelas</span>
                            <strong class="text-info">{{ $statistik['total_kelas'] }}</strong>
                        </div>
                        <small class="text-muted">Aktif: {{ $statistik['kelas_aktif'] }}</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-book-open text-success"></i> Mata Kuliah</span>
                            <strong class="text-success">{{ $statistik['total_mata_kuliah'] }}</strong>
                        </div>
                    </div>
                    <hr>
                    
                </div>
            </div>
        </div>
    </div>

    

    
</div>
@endsection

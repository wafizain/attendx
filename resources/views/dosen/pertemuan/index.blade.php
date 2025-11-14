@extends('layouts.master')

@section('title', 'Pertemuan - Dosen')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Daftar Pertemuan
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" action="{{ route('pertemuan.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="jadwal_id" class="form-label">Mata Kuliah</label>
                                <select name="jadwal_id" id="jadwal_id" class="form-select">
                                    <option value="">Semua Mata Kuliah</option>
                                    @foreach($jadwalList as $jadwal)
                                        <option value="{{ $jadwal->id }}" {{ request('jadwal_id') == $jadwal->id ? 'selected' : '' }}>
                                            {{ $jadwal->mataKuliah->nama }} - {{ $jadwal->kelas->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('pertemuan.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Mata Kuliah</th>
                                    <th>Kelas</th>
                                    <th>Pertemuan</th>
                                    <th>Jam</th>
                                    <th>Ruangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pertemuanList as $pertemuan)
                                    <tr>
                                        <td>{{ $pertemuan->tanggal->format('d M Y') }}</td>
                                        <td>{{ $pertemuan->jadwal->mataKuliah->nama }}</td>
                                        <td>{{ $pertemuan->jadwal->kelas->nama }}</td>
                                        <td>Pertemuan {{ $pertemuan->pertemuan_ke }}</td>
                                        <td>{{ $pertemuan->jam_mulai }} - {{ $pertemuan->jam_selesai }}</td>
                                        <td>{{ $pertemuan->ruangan->nama ?? '-' }}</td>
                                        <td>
                                            @switch($pertemuan->status)
                                                @case('scheduled')
                                                    <span class="badge bg-secondary">Terjadwal</span>
                                                    @break
                                                @case('active')
                                                    <span class="badge bg-success">Aktif</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-primary">Selesai</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Dibatalkan</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $pertemuan->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('pertemuan.show', $pertemuan->id) }}" class="btn btn-sm btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data pertemuan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $pertemuanList->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

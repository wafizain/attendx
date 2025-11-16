@extends('layouts.master')
@section('content')
<style>
    .clean-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .clean-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f9fafb;
        color: #6b7280;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 600;
        color: #111827;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .active-session-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    
    .session-number {
        font-size: 4rem;
        font-weight: 600;
        color: #111827;
        line-height: 1;
    }
    
    .table-clean {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-clean thead th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table-clean tbody tr {
        transition: background-color 0.15s ease;
    }
    
    .table-clean tbody tr:hover {
        background-color: #f9fafb;
    }
    
    .table-clean tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    
    .badge-clean {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
        border: 1px solid;
    }
    
    .badge-active {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }
    
    .badge-finished {
        background: #f9fafb;
        color: #374151;
        border-color: #e5e7eb;
    }
    
    .badge-draft {
        background: #fffbeb;
        color: #92400e;
        border-color: #fde68a;
    }
    
    .badge-cancelled {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }
    
    .welcome-section {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .welcome-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    
    .welcome-text {
        color: #6b7280;
        margin-bottom: 0;
    }
    
    .section-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.25rem;
        font-size: 1.125rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #9ca3af;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    .divider {
        height: 1px;
        background: #e5e7eb;
        margin: 1.5rem 0;
    }
    
    .text-primary-clean {
        color: #111827;
    }
    
    .text-secondary-clean {
        color: #6b7280;
    }
    
    .bg-light-clean {
        background: #f9fafb;
    }
    
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        text-decoration: none;
        color: #374151;
        transition: all 0.2s ease;
    }
    
    .quick-action-btn:hover {
        border-color: #111827;
        background: #f9fafb;
        color: #111827;
        transform: translateY(-2px);
    }
    
    .quick-action-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        border-radius: 8px;
        font-size: 1.25rem;
    }
    
    .progress-bar-clean {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: #111827;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    .attendance-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .attendance-stat:last-child {
        border-bottom: none;
    }
    
    .attendance-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .attendance-value {
        font-weight: 600;
        color: #111827;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">Dashboard Admin</h1>
                <p class="welcome-text">Selamat datang, <strong>{{ auth()->user()->name }}</strong></p>
                <small class="text-secondary-clean">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </small>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="text-secondary-clean small mb-1">Total Mahasiswa</div>
                <div class="h4 mb-0 text-primary-clean">{{ $totalUser }} <small class="text-secondary-clean fs-6">mahasiswa</small></div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Total Admin</div>
                            <div class="stat-number">{{ $totalAdmin }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-shield fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Total Dosen</div>
                            <div class="stat-number">{{ $totalDosen }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Total Mahasiswa</div>
                            <div class="stat-number">{{ $totalMahasiswa }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Total Kelas</div>
                            <div class="stat-number">{{ $totalKelas }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-door-open fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Total Mata Kuliah</div>
                            <div class="stat-number">{{ $totalMataKuliah }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-book fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Sesi Hari Ini</div>
                            <div class="stat-number">{{ $sesiHariIni }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Absensi Hari Ini</div>
                            <div class="stat-number">{{ $absensiHariIni }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="clean-card stat-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label mb-2">Kehadiran Hari Ini</div>
                            <div class="stat-number">{{ $persentaseKehadiranHariIni }}%</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="section-title">Quick Actions</h5>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Kelola User - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="fw-semibold">Kelola User</div>
                    <small class="text-secondary-clean">Manajemen pengguna</small>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Kelola Kelas - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div>
                    <div class="fw-semibold">Kelola Kelas</div>
                    <small class="text-secondary-clean">Manajemen kelas</small>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Mata Kuliah - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <div class="fw-semibold">Mata Kuliah</div>
                    <small class="text-secondary-clean">Manajemen matkul</small>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Sesi Absensi - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="fw-semibold">Sesi Absensi</div>
                    <small class="text-secondary-clean">Lihat semua sesi</small>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Laporan - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <div class="fw-semibold">Laporan</div>
                    <small class="text-secondary-clean">Lihat laporan</small>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6">
            <a href="#" class="quick-action-btn" onclick="alert('Fitur Pengaturan - Silakan hubungkan dengan route yang sesuai'); return false;">
                <div class="quick-action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <div class="fw-semibold">Pengaturan</div>
                    <small class="text-secondary-clean">Sistem settings</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Attendance Statistics & Chart -->
    <div class="row g-3 mb-4">
        <!-- Attendance Today -->
        <div class="col-xl-4 col-lg-5">
            <div class="clean-card">
                <div class="card-body p-4">
                    <h5 class="section-title mb-3">Statistik Kehadiran Hari Ini</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary-clean">Tingkat Kehadiran</span>
                            <span class="fw-bold text-primary-clean">{{ $persentaseKehadiranHariIni }}%</span>
                        </div>
                        <div class="progress-bar-clean">
                            <div class="progress-fill" style="width: {{ $persentaseKehadiranHariIni }}%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                <span>Hadir</span>
                            </div>
                            <div class="attendance-value">{{ $hadirHariIni }}</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-file-alt" style="color: #f59e0b;"></i>
                                <span>Izin</span>
                            </div>
                            <div class="attendance-value">{{ $izinHariIni }}</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-notes-medical" style="color: #3b82f6;"></i>
                                <span>Sakit</span>
                            </div>
                            <div class="attendance-value">{{ $sakitHariIni }}</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-times-circle" style="color: #ef4444;"></i>
                                <span>Alpha</span>
                            </div>
                            <div class="attendance-value">{{ $alphaHariIni }}</div>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="text-center">
                        <div class="text-secondary-clean small mb-1">Total Absensi Hari Ini</div>
                        <div class="h4 mb-0 text-primary-clean">{{ $absensiHariIni }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="clean-card">
                <div class="card-body p-4">
                    <h5 class="section-title mb-3">Trend Kehadiran 7 Hari Terakhir</h5>
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions & Recent Sessions -->
    <div class="row g-3">
        <!-- Active Sessions Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="clean-card active-session-card">
                <div class="card-body p-4">
                    <h5 class="section-title mb-3">Sesi Absensi Aktif</h5>
                    
                    <div class="text-center py-4">
                        <div class="session-number mb-2">{{ $sesiAktif }}</div>
                        <p class="text-secondary-clean mb-0">Sesi sedang berlangsung</p>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-list"></i>
                                <span>Total Sesi Terbaru</span>
                            </div>
                            <div class="attendance-value">{{ $recentSesi->count() }}</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-label">
                                <i class="fas fa-calendar-day"></i>
                                <span>Sesi Hari Ini</span>
                            </div>
                            <div class="attendance-value">{{ $sesiHariIni }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Overall Statistics -->
            <div class="clean-card mt-3">
                <div class="card-body p-4">
                    <h5 class="section-title mb-3">Statistik Keseluruhan</h5>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary-clean small">Tingkat Kehadiran Total</span>
                            <span class="fw-bold">{{ $persentaseKehadiranTotal }}%</span>
                        </div>
                        <div class="progress-bar-clean">
                            <div class="progress-fill" style="width: {{ $persentaseKehadiranTotal }}%"></div>
                        </div>
                    </div>
                    
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="bg-light-clean p-2 rounded">
                                <div class="text-secondary-clean small">Total Absensi</div>
                                <div class="fw-bold">{{ $totalAbsensi }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light-clean p-2 rounded">
                                <div class="text-secondary-clean small">Total Hadir</div>
                                <div class="fw-bold">{{ $totalHadir }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Sessions Table -->
        <div class="col-xl-8 col-lg-7">
            <div class="clean-card">
                <div class="card-body p-4">
                    <h5 class="section-title mb-3">Sesi Absensi Terbaru</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-clean mb-0">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSesi as $sesi)
                                <tr>
                                    <td>
                                        <strong>{{ $sesi->kelas->nama_kelas ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        {{ $sesi->kelas?->mataKuliah?->nama_matkul ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $sesi->kelas?->dosen?->name ?? '-' }}
                                    </td>
                                    <td>
                                        <small class="text-secondary-clean">
                                            {{ $sesi->tanggal ? $sesi->tanggal->format('d/m/Y') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($sesi->status == 'aktif')
                                            <span class="badge-clean badge-active">Aktif</span>
                                        @elseif($sesi->status == 'selesai')
                                            <span class="badge-clean badge-finished">Selesai</span>
                                        @elseif($sesi->status == 'draft')
                                            <span class="badge-clean badge-draft">Draft</span>
                                        @else
                                            <span class="badge-clean badge-cancelled">Dibatalkan</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="mb-0">Belum ada sesi absensi</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [
                    {
                        label: 'Hadir',
                        data: {!! $chartHadir !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Izin',
                        data: {!! $chartIzin !!},
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Sakit',
                        data: {!! $chartSakit !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Alpha',
                        data: {!! $chartAlpha !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 13
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
});
</script>
@endsection

<!-- Modern Sidebar with Bootstrap 5 -->
<style>
  /* Sidebar Styles */
  .sidebar-user-panel {
    background: #F9FAFB;
    border-radius: 12px;
    padding: 0.75rem;
    margin: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #E5E7EB;
  }
  
  .sidebar-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
  }
  
  .sidebar-user-info {
    flex: 1;
    min-width: 0;
  }
  
  .sidebar-user-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: #111827;
    margin-bottom: 0.125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .sidebar-user-status {
    font-size: 0.75rem;
    color: #6B7280;
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }
  
  .status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10B981;
  }
  
  .sidebar-power-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #10B981;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    border: none;
  }
  
  .sidebar-power-btn:hover {
    background: #059669;
    transform: scale(1.05);
  }
  
  /* Menu Label */
  .menu-label {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #9CA3AF;
    padding: 1.25rem 1rem 0.5rem;
    margin: 0;
  }
  
  /* Menu Items */
  .sidebar-menu {
    list-style: none;
    padding: 0 0.75rem;
    margin: 0;
  }
  
  .menu-item {
    margin-bottom: 0.25rem;
  }
  
  .menu-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.875rem;
    border-radius: 10px;
    color: #6B7280;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
  }
  
  .menu-link:hover {
    background: #F3F4F6;
    color: #111827;
  }
  
  .menu-link.active {
    background: #EEF2FF; /* light background tetap */
    color: #0e4a95;      /* color aktif sesuai permintaan */
    font-weight: 600;
    position: relative;
    border: 1px solid #E5E7EB;
  }

  /* Matikan indikator lama di kiri */
  .menu-link.active::before { content: none; display: none; }

  /* Tambahkan bar vertikal di kanan */
  .menu-link.active::after {
    content: '';
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 28px;
    background: #0e4a95; /* bar vertikal warna sama dengan aktif */
    border-radius: 3px;
  }
  
  .menu-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
  }
  
  /* Submenu */
  .submenu {
    list-style: none;
    padding: 0;
    margin: 0.25rem 0 0.5rem 0;
    display: none;
  }
  
  .menu-item.open .submenu {
    display: block;
  }
  
  .submenu .menu-link {
    padding-left: 3rem;
    font-size: 0.8125rem;
  }
  
  .submenu .menu-icon {
    width: 16px;
    height: 16px;
    font-size: 0.875rem;
  }
  
  /* Chevron */
  .menu-chevron {
    margin-left: auto;
    font-size: 0.75rem;
    transition: transform 0.3s ease;
  }
  
  .menu-item.open .menu-chevron {
    transform: rotate(180deg);
  }
  
  /* Collapsed State */
  .custom-sidebar.collapsed .sidebar-user-info,
  .custom-sidebar.collapsed .sidebar-power-btn,
  .custom-sidebar.collapsed .menu-label,
  .custom-sidebar.collapsed .menu-link span,
  .custom-sidebar.collapsed .menu-chevron,
  .custom-sidebar.collapsed .submenu {
    display: none !important;
  }
  
  .custom-sidebar.collapsed .sidebar-user-panel {
    justify-content: center;
    padding: 0.5rem;
    width: 56px;
    margin: 0.75rem auto;
  }
  
  .custom-sidebar.collapsed .menu-link {
    width: 56px;
    height: 48px;
    margin: 0.25rem auto;
    padding: 0;
    justify-content: center;
  }
  
  .custom-sidebar.collapsed .menu-icon {
    margin: 0;
    font-size: 1.25rem;
  }
  
  /* Tooltip for collapsed */
  .menu-link[data-tooltip] {
    position: relative;
  }
  
  .custom-sidebar.collapsed .menu-link:hover::after {
    content: attr(data-tooltip);
    position: fixed;
    left: 90px;
    background: #1F2937;
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 9999;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    pointer-events: none;
  }
  
  .custom-sidebar.collapsed .menu-link:hover::before {
    content: '';
    position: fixed;
    left: 85px;
    border: 5px solid transparent;
    border-right-color: #1F2937;
    z-index: 9999;
    pointer-events: none;
  }

  /* Sidebar Brand */
  .sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 1rem;
    margin: 0.5rem 0.75rem 0.25rem;
    border-radius: 10px;
  }

  .sidebar-brand img {
    width: 36px;
    height: 36px;
    object-fit: contain;
    border-radius: 8px;
  }

  .sidebar-brand span {
    font-weight: 700;
    color: #111827;
    font-size: 1rem;
  }

  /* Collapsed behavior for brand */
  .custom-sidebar.collapsed .sidebar-brand {
    justify-content: center;
    padding: 0.75rem 0.5rem;
  }
  .custom-sidebar.collapsed .sidebar-brand span { display: none; }
  .custom-sidebar.collapsed .sidebar-brand img { width: 32px; height: 32px; }

  /* Responsive brand sizes */
  @media (max-width: 992px) {
    .sidebar-brand { padding: 0.75rem; }
    .sidebar-brand img { width: 32px; height: 32px; }
    .sidebar-brand span { font-size: 0.95rem; }
  }
  @media (max-width: 576px) {
    .sidebar-brand { padding: 0.5rem 0.75rem; }
    .sidebar-brand img { width: 28px; height: 28px; }
    .sidebar-brand span { font-size: 0.9rem; }
  }
</style>

<!-- Sidebar -->
<aside class="custom-sidebar" id="customSidebar">
  <div class="sidebar-brand">
    <img src="{{ asset('images/logo-attendx.png') }}" alt="AttendX">
    <span>AttendX</span>
  </div>
  
    
    
    <ul class="sidebar-menu">
      <li class="menu-item">
        <a href="{{ route('dashboard') }}" class="menu-link" data-tooltip="Dashboard">
          <i class="menu-icon fas fa-th-large"></i>
          <span>Dashboard</span>
        </a>
      </li>
      
      @if(auth()->user()->role == 'admin')
      <!-- Admin Menu -->
      
      <!-- Data Master -->
      <li class="menu-item">
        <a href="#" class="menu-link" data-tooltip="Data Master" onclick="toggleSubmenu(this); return false;">
          <i class="menu-icon fas fa-database"></i>
          <span>Data Master</span>
          <i class="fas fa-chevron-down menu-chevron"></i>
        </a>
        <ul class="submenu">
          <li class="menu-item">
            <a href="{{ route('prodi.index') }}" class="menu-link">
              <i class="menu-icon fas fa-university"></i>
              <span>Program Studi</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('mata-kuliah.index') }}" class="menu-link">
              <i class="menu-icon fas fa-book-open"></i>
              <span>Mata Kuliah</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('kelas.index') }}" class="menu-link">
              <i class="menu-icon fas fa-door-open"></i>
              <span>Kelas</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('ruangan.index') }}" class="menu-link">
              <i class="menu-icon fas fa-building"></i>
              <span>Ruangan</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('admin.dosen.index') }}" class="menu-link">
              <i class="menu-icon fas fa-chalkboard-teacher"></i>
              <span>Dosen</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('mahasiswa.index') }}" class="menu-link">
              <i class="menu-icon fas fa-user-graduate"></i>
              <span>Mahasiswa</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('arsip.index') }}" class="menu-link">
              <i class="menu-icon fas fa-archive"></i>
              <span>Arsip</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('jadwal.index') }}" class="menu-link">
              <i class="menu-icon fa-solid fa-calendar-days"></i>
              <span>Jadwal Kuliah</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- Laporan -->
      <li class="menu-item">
        <a href="#" class="menu-link" data-tooltip="Laporan" onclick="toggleSubmenu(this); return false;">
          <i class="menu-icon fa-solid fa-clipboard-list"></i>
          <span>Laporan</span>
          <i class="fas fa-chevron-down menu-chevron"></i>
        </a>
        <ul class="submenu">
          <li class="menu-item">
            <a href="{{ route('reports.by-student') }}" class="menu-link">
              <i class="menu-icon fas fa-file-lines"></i>
              <span>Rekap Absensi Mahasiswa</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('reports.by-class') }}" class="menu-link">
              <i class="menu-icon fas fa-book-open"></i>
              <span>Rekap Absensi Per Mata Kuliah</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('logs.index') }}" class="menu-link">
              <i class="menu-icon fas fa-history"></i>
              <span>Log Aktivitas</span>
            </a>
          </li>
        </ul>
      </li>
      
      <!-- Perangkat -->
      <li class="menu-item">
        <a href="#" class="menu-link" data-tooltip="Perangkat" onclick="toggleSubmenu(this); return false;">
          <i class="menu-icon fas fa-microchip"></i>
          <span>Perangkat & Integrasi</span>
          <i class="fas fa-chevron-down menu-chevron"></i>
        </a>
        <ul class="submenu">
          <li class="menu-item">
            <a href="{{ route('devices.index') }}" class="menu-link">
              <i class="menu-icon fa-solid fa-microchip"></i>
              <span>Perangkat Absensi</span>
            </a>
          </li>
          <li class="menu-item">
            <a href="{{ route('logs.device') }}" class="menu-link">
              <i class="menu-icon fa-solid fa-plug-circle-check"></i>
              <span>Log Perangkat</span>
            </a>
          </li>
        </ul>
      </li>
      
      <!-- Manajemen Akun -->
      <li class="menu-item">
        <a href="#" class="menu-link" data-tooltip="Manajemen Akun" onclick="toggleSubmenu(this); return false;">
          <i class="menu-icon fas fa-users-cog"></i>
          <span>Manajemen Akun</span>
          <i class="fas fa-chevron-down menu-chevron"></i>
        </a>
        <ul class="submenu">
          <li class="menu-item">
            <a href="{{ route('admin.index') }}" class="menu-link">
              <i class="menu-icon fa-solid fa-user-shield"></i>
              <span>Akun Admin</span>
            </a>
          </li>
        </ul>
      </li>
      
      
      @endif

      @if(auth()->user()->role == 'dosen')
      @php
         // Cari sesi absensi aktif yang terkait dengan jadwal kuliah dosen saat ini
         $activeSesiDosen = \App\Models\SesiAbsensi::where('status', 'aktif')
             ->whereHas('kelas.jadwalKuliah', function($q) {
                 $q->where('id_dosen', auth()->id());
             })
             ->with(['kelas' => function($q) {
                 $q->with(['mataKuliah', 'jadwalKuliah' => function($q) {
                     $q->where('id_dosen', auth()->id());
                 }]);
             }])
             ->orderByDesc('tanggal')
             ->first();
      @endphp
      <!-- Dosen Menu -->
      <li class="menu-item">
        <a href="{{ route('dosen.jadwal-mengajar.index') }}" class="menu-link" data-tooltip="Jadwal Mengajar">
          <i class="menu-icon fa-solid fa-chalkboard-teacher"></i>
          <span>Jadwal Mengajar</span>
        </a>
      </li>
      @if($activeSesiDosen)
      <li class="menu-item">
        <a href="{{ route('dosen.jadwal-mengajar.sesi', $activeSesiDosen->id) }}" class="menu-link" data-tooltip="Pertemuan Berlangsung">
          <i class="menu-icon fa-solid fa-circle-play text-success"></i>
          <span>Pertemuan Berlangsung</span>
        </a>
      </li>
      @endif
      <li class="menu-item">
        <a href="{{ route('dosen.absen-manual') }}" class="menu-link" data-tooltip="Absen Manual">
          <i class="menu-icon fa-solid fa-edit"></i>
          <span>Absen Manual</span>
        </a>
      </li>
      <li class="menu-item">
        <a href="{{ route('dosen.rekap-absensi.index') }}" class="menu-link" data-tooltip="Rekap Absensi Mahasiswa">
          <i class="menu-icon fa-solid fa-chart-line"></i>
          <span>Rekap Absensi Mahasiswa</span>
        </a>
      </li>
      <li class="menu-item">
        <a href="{{ route('dosen.riwayat-pertemuan.index') }}" class="menu-link" data-tooltip="Riwayat Pertemuan">
          <i class="menu-icon fa-solid fa-clock-rotate-left"></i>
          <span>Riwayat Pertemuan</span>
        </a>
      </li>
      @endif

      @if(auth()->user()->role == 'mahasiswa')
      <!-- Mahasiswa Menu -->
      <li class="menu-item">
        <a href="{{ route('mahasiswa.jadwal') }}" class="menu-link" data-tooltip="Jadwal Kuliah">
          <i class="menu-icon fa-solid fa-calendar-days"></i>
          <span>Jadwal Kuliah</span>
        </a>
      </li>
      <li class="menu-item">
        <a href="{{ route('mahasiswa.absensi') }}" class="menu-link" data-tooltip="Riwayat Absensi">
          <i class="menu-icon fas fa-history"></i>
          <span>Riwayat Absensi</span>
        </a>
      </li>
      <li class="menu-item">
        <a href="{{ Route::has('izin.create') ? route('izin.create') : '#' }}" class="menu-link" data-tooltip="Ajukan Izin">
          <i class="menu-icon fa-solid fa-file-medical"></i>
          <span>Ajukan Izin</span>
        </a>
      </li>
      @endif
    </ul>
    
    @if(auth()->user()->role == 'admin')
    @endif

  </div>
</aside>

<!-- JavaScript -->
<script>
  // Toggle submenu
  function toggleSubmenu(element) {
    const menuItem = element.closest('.menu-item');
    menuItem.classList.toggle('open');
  }
  
  // Toggle sidebar
  function toggleSidebar() {
    const sidebar = document.getElementById('customSidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
  }
  
  // Set active menu
  document.addEventListener('DOMContentLoaded', function() {
    const currentUrl = window.location.href;
    const menuLinks = document.querySelectorAll('.menu-link');
    
    menuLinks.forEach(link => {
      if (link.href === currentUrl) {
        link.classList.add('active');
        
        // Open parent submenu if exists
        const parentSubmenu = link.closest('.submenu');
        if (parentSubmenu) {
          const parentMenuItem = parentSubmenu.closest('.menu-item');
          parentMenuItem.classList.add('open');
        }
      }
    });
  });
</script>

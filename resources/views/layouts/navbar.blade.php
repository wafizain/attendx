<!-- Modern Navbar with Bootstrap 5 -->
<div class="custom-navbar d-flex align-items-center justify-content-between">
  <!-- Hamburger Button -->
  <button class="btn btn-link text-dark p-0" onclick="toggleSidebar()" style="font-size: 1.25rem;">
    <i class="fas fa-bars"></i>
  </button>
  
  <!-- Page Title (Center - Responsive) -->
  <div class="navbar-title-center d-flex align-items-center gap-2">
    @php
      $icon = 'fa-home';
      if (request()->routeIs('dosen.*')) {
        $icon = 'fa-user-tie';
      } elseif (request()->routeIs('ruangan.*')) {
        $icon = 'fa-door-open';
      } elseif (request()->routeIs('mahasiswa.*')) {
        $icon = 'fa-user-graduate';
      }
    @endphp
    <i class="fas {{ $icon }} text-muted"></i>
    <span class="fw-semibold">@yield('page-title', 'Dashboard')</span>
  </div>
  
  <!-- Right: User + Collapse/Dropdown -->
  <div class="d-flex align-items-center gap-2">
    <span class="text-muted small d-none d-md-inline">{{ auth()->user()->name ?? 'User' }}</span>
    <div class="dropdown">
      <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown" aria-expanded="false" title="Menu">
        <i class="fas fa-chevron-down"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="#" id="btn-logout">Logout</a></li>
      </ul>
    </div>
  </div>
</div>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

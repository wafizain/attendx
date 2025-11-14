<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Sistem Absensi Mahasiswa') | SIABSEN</title>
  <meta name="description" content="Sistem Informasi Absensi Mahasiswa dengan IoT">

  <!-- Google Fonts: Inter (Modern & Clean) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Custom Bootstrap 5 Styles -->
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background: #F3F4F6;
      overflow-x: hidden;
    }
    
    /* Sidebar */
    .custom-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 260px;
      height: 100vh;
      background: white;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
      border-right: 1px solid #E5E7EB;
      transition: width 0.3s ease, transform 0.3s ease;
      z-index: 1040;
      overflow-y: auto;
      overflow-x: hidden;
    }
    
    .custom-sidebar.collapsed {
      width: 80px;
    }
    
    /* Main Content */
    .main-content {
      margin-left: 260px;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
      padding: 20px;
    }
    
    .main-content.expanded {
      margin-left: 80px;
    }
    
    /* Navbar */
    .custom-navbar {
      background: white;
      border-bottom: 1px solid #E5E7EB;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      position: relative;
    }
    
    /* Navbar title center - responsive to viewport */
    .navbar-title-center {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      transition: all 0.3s ease;
    }
    
    /* Mobile */
    @media (max-width: 768px) {
      .custom-sidebar {
        transform: translateX(-100%);
      }
      
      .custom-sidebar.show {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      /* Center title on mobile (no sidebar offset) */
      body .navbar-title-center {
        left: 50% !important;
      }
    }
  </style>
  
  <!-- Custom Modern Styles -->
  <style>
    :root {
      --primary-color: #4F46E5;
      --primary-dark: #4338CA;
      --primary-light: #818CF8;
      --secondary-color: #10B981;
      --accent-color: #F59E0B;
      --danger-color: #EF4444;
      --warning-color: #F59E0B;
      --info-color: #3B82F6;
      --success-color: #10B981;
      --dark-color: #1F2937;
      --light-bg: #F9FAFB;
      --border-color: #E5E7EB;
      --text-primary: #111827;
      --text-secondary: #6B7280;
      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
      background: var(--light-bg);
      color: var(--text-primary);
      font-size: 14px;
      line-height: 1.6;
    }

    /* Smooth Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #F3F4F6;
    }

    ::-webkit-scrollbar-thumb {
      background: #D1D5DB;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #9CA3AF;
    }

    /* Content Wrapper */
    .content-wrapper {
      background: var(--light-bg);
      min-height: calc(100vh - 57px);
    }
    
    /* Navbar z-index fix */
    .main-header.navbar {
      z-index: 1040 !important;
    }
    
    /* Sidebar z-index */
    .main-sidebar {
      z-index: 1035 !important;
    }

    /* Card Modern */
    .card {
      border: none;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      margin-bottom: 1.5rem;
    }

    .card:hover {
      box-shadow: var(--shadow-md);
    }

    .card-header {
      background: white;
      border-bottom: 1px solid var(--border-color);
      border-radius: var(--radius-md) var(--radius-md) 0 0 !important;
      padding: 1rem 1.25rem;
    }

    .card-title {
      font-weight: 600;
      font-size: 1rem;
      color: var(--text-primary);
      margin: 0;
    }

    .card-body {
      padding: 1.25rem;
    }

    /* Buttons Modern */
    .btn {
      border-radius: var(--radius-sm);
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: var(--transition);
      border: none;
      font-size: 0.875rem;
    }

    .btn-primary {
      background: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }

    .btn-success {
      background: var(--success-color);
    }

    .btn-success:hover {
      background: #059669;
      transform: translateY(-1px);
    }

    .btn-danger {
      background: var(--danger-color);
    }

    .btn-danger:hover {
      background: #DC2626;
      transform: translateY(-1px);
    }

    .btn-warning {
      background: var(--warning-color);
      color: white;
    }

    .btn-info {
      background: var(--info-color);
    }

    /* Form Controls */
    .form-control, .form-select {
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 0.5rem 0.75rem;
      transition: var(--transition);
      font-size: 0.875rem;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    /* Tables */
    .table {
      font-size: 0.875rem;
    }

    .table thead th {
      background: var(--light-bg);
      color: var(--text-secondary);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
      border-bottom: 2px solid var(--border-color);
      padding: 0.75rem 1rem;
    }

    .table tbody tr {
      transition: var(--transition);
    }

    .table tbody tr:hover {
      background: #F9FAFB;
    }

    /* Badges Modern */
    .badge {
      padding: 0.35rem 0.65rem;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.75rem;
    }

    /* Alert Modern */
    .alert {
      border: none;
      border-radius: var(--radius-sm);
      padding: 1rem 1.25rem;
      border-left: 4px solid;
    }

    .alert-success {
      background: #ECFDF5;
      color: #065F46;
      border-left-color: var(--success-color);
    }

    .alert-danger {
      background: #FEF2F2;
      color: #991B1B;
      border-left-color: var(--danger-color);
    }

    .alert-warning {
      background: #FFFBEB;
      color: #92400E;
      border-left-color: var(--warning-color);
    }

    .alert-info {
      background: #EFF6FF;
      color: #1E40AF;
      border-left-color: var(--info-color);
    }

    /* Breadcrumb */
    .breadcrumb {
      background: transparent;
      padding: 0;
      margin: 0;
      font-size: 0.875rem;
    }

    .breadcrumb-item {
      color: var(--text-secondary);
    }

    .breadcrumb-item.active {
      color: var(--text-primary);
      font-weight: 500;
    }

    /* Content Header */
    .content-header {
      padding: 1.5rem 1.5rem 1rem;
      background: white;
      margin-bottom: 1.5rem;
      border-bottom: 1px solid var(--border-color);
    }

    .content-header h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
      margin: 0;
    }

    /* Modal Modern */
    .modal-content {
      border: none;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-xl);
    }

    .modal-header {
      border-bottom: 1px solid var(--border-color);
      padding: 1.25rem 1.5rem;
    }

    .modal-title {
      font-weight: 600;
      font-size: 1.125rem;
    }

    /* Info Box Modern */
    .info-box {
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      border: none;
      transition: var(--transition);
    }

    .info-box:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .info-box-icon {
      border-radius: var(--radius-md) 0 0 var(--radius-md);
    }

    /* Preloader Modern */
    .preloader {
      background: white;
    }

    /* Animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .content {
      animation: fadeIn 0.3s ease-in-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .content-header h1 {
        font-size: 1.25rem;
      }
      
      .card-body {
        padding: 1rem;
      }
    }
  </style>
  
  @stack('styles')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="wrapper">
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Logout?',
          text: 'Anda akan keluar dari sesi saat ini.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#0e4a95',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'Ya, logout',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            const form = document.getElementById('logout-form');
            if (form) form.submit();
          }
        });
      });
    }
  });
</script>
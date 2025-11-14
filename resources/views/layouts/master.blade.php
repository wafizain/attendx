@include('layouts.header')

@include('layouts.sidebar')

<!-- Main Content -->
<div class="main-content" id="mainContent">
  @include('layouts.navbar')
  
  <!-- Page Content -->
  <div class="container-fluid">
    @yield('content')
  </div>
</div>

@include('layouts.footer')
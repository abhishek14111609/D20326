<!doctype html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <meta name="theme-color" content="#ffffff">
  <title>@yield('title', config('app.name', 'Laravel'))</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="https://duos.webvibeinfotech.in/public/assets/img/favicon/app_logo.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Boxicons -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

  <!-- Core CSS -->
  <link rel="stylesheet" href="https://duos.webvibeinfotech.in/public/assets/vendor/css/core.css" />
  <link rel="stylesheet" href="https://duos.webvibeinfotech.in/public/assets/css/demo.css" />
  <link rel="stylesheet" href="https://duos.webvibeinfotech.in/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="https://duos.webvibeinfotech.in/public/assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Custom CSS -->
  <link href="https://duos.webvibeinfotech.in/public/css/admin.css" rel="stylesheet">

  @stack('styles')

  <!-- Helpers -->
  <script src="https://duos.webvibeinfotech.in/public/assets/vendor/js/helpers.js"></script>
  <script src="https://duos.webvibeinfotech.in/public/assets/js/config.js"></script>
</head>
<style>
  .stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;   /* adjust size */
  height: 60px;  /* adjust size */
  border-radius: 50%;
  font-size: 28px; /* icon size */
  color: #fff;    /* icon color */
}
</style>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        @include('admin.layouts.sidebar')
        <div class="layout-page">
          @include('admin.layouts.navbar')

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
              @yield('content')
            </div>
          </div>
        </div>
      </div>
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/js/bootstrap.js"></script>
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/js/menu.js"></script>

    <!-- Vendors JS -->
    <script src="https://duos.webvibeinfotech.in/public/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="https://duos.webvibeinfotech.in/public/assets/js/main.js"></script>

    @stack('page-js')

    <!-- Custom Scripts -->
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>

    @stack('scripts')
  </body>
</html>

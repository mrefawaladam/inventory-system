<!DOCTYPE html>
<html lang="id" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @stack('styles')
  <title>@yield('title', 'Inventory System')</title>
</head>
<body>
  <div id="main-wrapper">
    <div class="page-wrapper">
      <div class="body-wrapper">
        <div class="container-fluid">
          @yield('content')
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  @include('components.layout.scripts')
  @stack('scripts')
</body>
</html>


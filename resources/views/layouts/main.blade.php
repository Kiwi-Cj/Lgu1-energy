<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LGU Portal')</title>
    <link rel="stylesheet" href="{{ asset('css/style - Copy.css') }}">
</head>
<body>
    <!-- Mobile sidebar toggle button -->
    <button class="d-block d-md-none btn btn-primary" style="position:fixed;top:10px;left:10px;z-index:300;" onclick="document.querySelector('.sidebar-nav').classList.toggle('open')">
        ☰
    </button>
    @include('partials.sidebar')
    <div class="main-content-wrapper">
        @yield('content')
    </div>
    <script>
    // Close sidebar on click outside (mobile)
    document.addEventListener('click', function(e) {
        var sidebar = document.querySelector('.sidebar-nav');
        var btn = document.querySelector('button[onclick*="sidebar-nav"]');
        if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== btn) {
            sidebar.classList.remove('open');
        }
    });
    </script>
</body>
</html>

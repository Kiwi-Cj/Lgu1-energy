<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LGU Portal')</title>
    <link rel="stylesheet" href="{{ asset('css/style - Copy.css') }}">
</head>
<body>
    @include('partials.sidebar')
    <div class="main-content-wrapper">
        @yield('content')
    </div>
</body>
</html>

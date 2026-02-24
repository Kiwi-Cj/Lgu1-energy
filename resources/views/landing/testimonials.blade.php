<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Testimonials | Energy System Portal</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef2ff; color: #1f2937; }
        .navbar { background: rgba(255,255,255,0.98); box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .navbar-brand img { height: 38px; margin-right: 10px; }
        .hero { background: linear-gradient(135deg, #0f172a, #1e3a8a); color: #fff; padding: 64px 0 48px; }
        .quote-card { background: #fff; border-radius: 18px; box-shadow: 0 10px 24px rgba(15,23,42,0.08); padding: 24px; height: 100%; }
        .author { color: #1d4ed8; font-weight: 600; margin-top: 12px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('img/logocityhall.jpg') }}" alt="Logo">
            <span class="fw-bold" style="font-size:1.1rem;">Energy System Portal</span>
        </a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ url('/') }}">Home</a>
            <a class="btn btn-primary" href="{{ url('/login') }}">Login</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">User Testimonials</h1>
        <p class="mb-0">Official testimonials will be published here after review and approval.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="quote-card text-center">
                    <h2 class="h4 mb-3">Testimonials Coming Soon</h2>
                    <p class="mb-2 text-muted">
                        We removed sample testimonials to avoid showing unverified statements.
                    </p>
                    <p class="mb-0 text-muted">
                        Once approved by the LGU/team, real user feedback will be displayed on this page.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

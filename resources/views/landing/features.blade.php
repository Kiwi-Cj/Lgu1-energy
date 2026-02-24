<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Features | Energy System Portal</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6fa; color: #1f2937; }
        .navbar { background: rgba(255,255,255,0.98); box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .navbar-brand img { height: 38px; margin-right: 10px; }
        .hero { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; padding: 64px 0 48px; }
        .feature-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 24px rgba(0,0,0,0.06); height: 100%; padding: 24px; }
        .feature-icon { font-size: 2rem; color: #2563eb; margin-bottom: 12px; }
        .feature-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 8px; }
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
        <h1 class="fw-bold mb-3">Platform Features</h1>
        <p class="mb-0">A quick overview of the tools available in the Energy System Portal.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="feature-title">Energy Monitoring</div>
                    <p class="text-muted mb-0">Track and analyze energy usage across facilities in real time.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="fa-solid fa-building"></i></div>
                    <div class="feature-title">Facility Management</div>
                    <p class="text-muted mb-0">Manage and maintain energy-related facilities efficiently.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="feature-title">Reports & Analytics</div>
                    <p class="text-muted mb-0">Generate reports and insights for better decisions.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <div class="feature-title">Secure Access</div>
                    <p class="text-muted mb-0">Role-based access for staff and administrators.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Energy System Portal</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}" /> 
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6fa;
        }
        .navbar {
            background: rgba(255,255,255,0.98);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .navbar-brand img {
            height: 38px;
            margin-right: 10px;
        }
        .hero-section {
            min-height: 90vh;
            background: url("{{ asset('img/energy illustration.jpg') }}") center center/cover no-repeat;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 18px;
            animation: fadeInDown 1s;
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 32px;
            color: #e0e7ef;
            animation: fadeInUp 1.2s;
        }
        .hero-section .btn-primary {
            padding: 14px 38px;
            font-size: 1.1rem;
            border-radius: 30px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(55,98,200,0.12);
            animation: fadeInUp 1.4s;
        }
        .hero-illustration {
            width: 100%;
            max-width: 480px;
            margin: 40px auto 0 auto;
            animation: fadeIn 2s;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .features-section {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            margin-top: -60px;
            padding: 48px 0 32px 0;
            position: relative;
            z-index: 2;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #3762c8;
            margin-bottom: 18px;
        }
        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .testimonials-section {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            margin-top: 40px;
            padding: 40px 0 32px 0;
        }
        .testimonial {
            font-size: 1.08rem;
            color: #374151;
            margin-bottom: 18px;
        }
        .testimonial-author {
            font-weight: 600;
            color: #3762c8;
            font-size: 1rem;
        }
        .footer {
            background: #22223b;
            color: #e0e7ef;
            padding: 32px 0 18px 0;
            text-align: center;
            font-size: 1rem;
            margin-top: 48px;
        }
        .footer a { color: #a5b4fc; text-decoration: underline; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="{{ asset('img/logocityhall.jpg') }}" alt="Logo">
            <span class="fw-bold" style="font-size:1.25rem;">Energy System Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('landing.features') }}">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('landing.testimonials') }}">Testimonials</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('landing.contact') }}">Contact</a></li>
    
                   <a class="btn btn-primary ms-lg-3" href="{{ url('/login') }}">Login</a>
                
            </ul>
        </div>
    </div>
</nav>
<section class="hero-section">
    <div class="container">
        <h1>Energy System Portal</h1>
        <p>Monitor, analyze, and manage your energy records and facilities in one secure, user-friendly platform.</p>
        <a href="{{ route('landing.features') }}" class="btn btn-primary">Explore Features</a>
    </div>
</section>
<section class="features-section" id="features">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="feature-icon"><i class="fa-solid fa-bolt"></i></div>
                <div class="feature-title">Energy Monitoring</div>
                <div class="text-muted">Track and analyze energy usage across all facilities in real time.</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon"><i class="fa-solid fa-building"></i></div>
                <div class="feature-title">Facility Management</div>
                <div class="text-muted">Manage, maintain, and optimize all energy-related facilities efficiently.</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="feature-title">Reports & Analytics</div>
                <div class="text-muted">Generate reports and gain insights for better decision-making.</div>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div class="feature-title">Secure Access</div>
                <div class="text-muted">Role-based access for staff and administrators with strong security.</div>
            </div>
        </div>
    </div>
</section>
<section class="testimonials-section" id="testimonials">
    <div class="container">
        <h3 class="text-center mb-5">What Our Users Say</h3>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="testimonial">"The Energy System Portal made our facility monitoring so much easier and more transparent. Highly recommended!"</div>
                <div class="testimonial-author">— Facility Manager, City Hall</div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">"We love the analytics and reporting features. It helps us make data-driven decisions for energy savings."</div>
                <div class="testimonial-author"> Energy Officer, Public School</div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">"Secure and easy to use. Our staff can now access records anytime, anywhere."</div>
                <div class="testimonial-author">— Admin, Local Government</div>
            </div>
        </div>
    </div>
</section>
<section class="container my-5" id="contact">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <h2 class="mb-3">Get in Touch</h2>
            <p class="mb-4 text-muted">Have a concern or inquiry? Send us a message and our team will respond as soon as possible to assist you.</p>
            @if (session('contact_success'))
                <div class="alert alert-success text-start">{{ session('contact_success') }}</div>
            @endif
            @if (session('contact_warning'))
                <div class="alert alert-warning text-start">{{ session('contact_warning') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger text-start">Please review the highlighted fields and try again.</div>
            @endif
            <form method="POST" action="{{ route('landing.contact.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" required>
                        @error('name')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required>
                        @error('email')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control @error('subject') is-invalid @enderror" placeholder="Subject (optional)">
                        @error('subject')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" placeholder="Your Message" required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="container">
        <div class="mb-2">© 2026 Energy System Portal · All Rights Reserved</div>
        <div>For support, email <a href="mailto:support@energysystem.com">support@energysystem.com</a> or call <a href="tel:+15551234567">+1 (555) 123-4567</a></div>
        <div class="mt-2">
            <a href="#" class="me-3"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="me-3"><i class="fab fa-twitter fa-lg"></i></a>
            <a href="#"><i class="fab fa-linkedin fa-lg"></i></a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



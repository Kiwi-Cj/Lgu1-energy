<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact | Energy System Portal</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #1f2937; }
        .navbar { background: rgba(255,255,255,0.98); box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .navbar-brand img { height: 38px; margin-right: 10px; }
        .hero { background: linear-gradient(135deg, #0f766e, #14b8a6); color: #fff; padding: 64px 0 48px; }
        .contact-card { background: #fff; border-radius: 18px; box-shadow: 0 12px 28px rgba(0,0,0,0.06); padding: 28px; }
    </style>
</head>
<body>
@php
    $supportEmail = env('ADMIN_SUPPORT_EMAIL', 'energyconservemgmt@gmail.com');
    $supportPhone = env('ADMIN_SUPPORT_PHONE', '+1 (555) 123-4567');
    $supportPhoneHref = preg_replace('/[^0-9+]/', '', $supportPhone);
@endphp

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
        <h1 class="fw-bold mb-3">Contact Us</h1>
        <p class="mb-0">Send your concerns and inquiries to the Energy System Portal support team.</p>
    </div>
</section>

<section class="py-5" id="contact">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-7">
                <div class="contact-card">
                    <h2 class="h4 mb-3">Get in Touch</h2>
                    <p class="text-muted mb-4">Have a concern or inquiry? Send us a message and our team will respond as soon as possible.</p>
                    @if (session('contact_success'))
                        <div class="alert alert-success">{{ session('contact_success') }}</div>
                    @endif
                    @if (session('contact_warning'))
                        <div class="alert alert-warning">{{ session('contact_warning') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">Please review the highlighted fields and try again.</div>
                    @endif
                    <form method="POST" action="{{ route('landing.contact.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control @error('subject') is-invalid @enderror" placeholder="Subject (optional)">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" placeholder="Your Message" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-4 text-muted">
                        <div>Email: <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></div>
                        <div>Phone: <a href="tel:{{ $supportPhoneHref }}">{{ $supportPhone }}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

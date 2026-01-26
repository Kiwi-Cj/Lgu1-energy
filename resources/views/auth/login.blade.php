<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LGU | Login</title>
<link rel="stylesheet" href="{{ asset('css/style - Copy.css') }}">
<style>

body {
    height: 100vh;
    display: flex;
    flex-direction: column;

    /* NEW — background image + blur */
    background: url("{{ asset('img/cityhall.jpeg') }}") center/cover no-repeat fixed;
    position: relative;
    overflow: hidden;
}

/* NEW — Blur overlay */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    backdrop-filter: blur(6px); /* actual blur */
    background: rgba(0, 0, 0, 0.35); /* dark overlay */
    z-index: 0; /* keeps blur behind content */
}

/* Make content appear ABOVE blur */
.nav, .wrapper {
    position: relative;
    z-index: 1;
}

/* Make content appear ABOVE blur */
.footer, .wrapper {
    position: relative;
    z-index: 1;
}
</style>
</head>

<body>

<header class="nav">
    <div class="nav-logo">🏛️ Local Government Unit Portal</div>
    <div class="nav-links">
        <a href="">Home</a>
    </div>
</header>

<div class="wrapper">
    <div class="card">  

        <img src="{{ asset('img/logocityhall.png') }}" class="icon-top">

        <h2 class="title">LGU Login</h2>
        <p class="subtitle">Secure access to community maintenance services.</p>

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@lgu.gov.ph" required>
                <span class="icon">📧</span>
            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
                <span class="icon">🔒</span>
            </div>

            <button class="btn-primary" type="submit">Sign In</button>

            <!-- Registration link removed -->
        </form>
    </div>
</div>

<footer class="footer">

    <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">About</a>
        <a href="#">Help</a>
    </div>

    <div class="footer-logo">
        © 2025 LGU Citizen Portal · All Rights Reserved
    </div>

</footer>

<script src="{{ asset('js/scripts.js') }}"></script>
</body>
</html>

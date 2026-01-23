<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account | LGU Portal</title>
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
</style>
</head>

<body>

<header class="nav">
    <div class="nav-logo">🏛️ LGU Portal</div>
    <div class="nav-links">
        <a href="{{ url('/login') }}">Login</a>
        <a class="active">Create Account</a>
    </div>
</header>

<div class="wrapper">
    <div class="card">

        <img src="{{ asset('img/logocityhall.png') }}" class="icon-top">

        <h2 class="title">Create Account</h2>
        <p class="subtitle">Register to access the LGU maintenance system.</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf


            <div class="input-box">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
                <span class="icon">👤</span>
            </div>



            <div class="input-box">
                <label>Username</label>
                <input type="text" name="username" placeholder="Username" required>
                <span class="icon">👤</span>
            </div>

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


            <div class="input-box">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" required>
                <span class="icon">🔒</span>
            </div>

            <button class="btn-primary">Create Account</button>

            <p class="small-text">
                Already registered?
                <a href="{{ url('/login') }}" class="link">Sign In</a>
            </p>

        </form>
    </div>
</div>

</body>
</html>

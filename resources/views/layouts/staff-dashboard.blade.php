<!-- resources/views/layouts/modern.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Modern Layout')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: #f4f6fa;
            color: #222;
        }
        .header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 100;
        }
        .header .logo {
            font-weight: 700;
            color: #2563eb;
            font-size: 1.3rem;
            letter-spacing: 1px;
        }
        .header .nav {
            display: flex;
            gap: 24px;
        }
        .header .nav a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .header .nav a:hover {
            color: #2563eb;
        }
        .sidebar {
            position: fixed;
            top: 56px; left: 0; bottom: 0;
            width: 220px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            padding-top: 32px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 24px;
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #f1f5f9;
            border-left: 4px solid #2563eb;
            color: #2563eb;
        }
        .main-content {
            margin-top: 56px;
            margin-left: 220px;
            padding: 32px;
            min-height: calc(100vh - 56px);
            background: #f4f6fa;
            transition: margin 0.2s;
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 64px;
                padding-top: 16px;
            }
            .sidebar a span {
                display: none;
            }
            .main-content {
                margin-left: 64px;
            }
        }
        @media (max-width: 600px) {
            .header {
                padding: 0 12px;
            }
            .sidebar {
                top: 48px;
                width: 56px;
            }
            .main-content {
                margin-left: 56px;
                padding: 16px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MyApp</div>
        <nav class="nav">
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
        </nav>
    </div>
    <aside class="sidebar">
        <a href="#" class="active"><span>📊</span> <span style="margin-left:10px;">Dashboard</span></a>
        <a href="#"><span>👤</span> <span style="margin-left:10px;">Profile</span></a>
        <a href="#"><span>⚙️</span> <span style="margin-left:10px;">Settings</span></a>
    </aside>
    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>

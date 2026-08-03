<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $systemName }} helps local government teams monitor facilities, analyze energy use, and make informed efficiency decisions.">
    <meta name="theme-color" content="#0f172a">
    <script>
        try {
            if (localStorage.getItem('darkMode') === 'on') {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.style.colorScheme = 'dark';
            }
        } catch (e) {}
    </script>
    <title>{{ $systemName }}</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --indigo: #6366f1;
            --sky: #0ea5e9;
            --ink: #0f172a;
            --body: #334155;
            --muted: #64748b;
            --line: #e2e8f0;
            --soft: #f8fafc;
            --success: #15803d;
            --container: 1180px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 86px;
        }

        body {
            margin: 0;
            overflow-x: hidden;
            color: var(--body);
            background: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body.menu-open {
            overflow: hidden;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        img,
        svg {
            display: block;
        }

        a {
            color: inherit;
        }

        .container {
            width: min(calc(100% - 40px), var(--container));
            margin-inline: auto;
        }

        .site-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
            height: 78px;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.13);
            background: rgba(8, 17, 35, 0.24);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: height 180ms ease, background 180ms ease, box-shadow 180ms ease;
        }

        .site-header.is-scrolled {
            height: 68px;
            background: rgba(15, 23, 42, 0.94);
            box-shadow: 0 10px 30px rgba(2, 6, 23, 0.15);
        }

        .nav-wrap {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 11px;
            color: #fff;
            text-decoration: none;
            font-size: 0.96rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand img {
            width: 43px;
            height: 43px;
            flex: 0 0 auto;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.2);
        }

        .brand span {
            max-width: 330px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nav-area,
        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-area {
            gap: 14px;
        }

        .theme-toggle {
            width: 45px;
            height: 45px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            padding: 0;
            color: #bfdbfe;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.07);
            cursor: pointer;
            transition: color 160ms ease, background 160ms ease, transform 160ms ease;
        }

        .theme-toggle:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-2px);
        }

        .theme-toggle svg { width: 19px; height: 19px; }
        .theme-toggle .sun-icon { display: none; }
        html.dark-mode .theme-toggle .moon-icon { display: none; }
        html.dark-mode .theme-toggle .sun-icon { display: block; }

        .nav-links {
            gap: 2px;
            padding: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.055);
        }

        .nav-links a {
            position: relative;
            min-height: 37px;
            display: inline-flex;
            align-items: center;
            padding: 8px 13px;
            color: rgba(255, 255, 255, 0.76);
            border-radius: 9px;
            text-decoration: none;
            font-size: 0.73rem;
            font-weight: 700;
            transition: color 160ms ease, background 160ms ease, transform 160ms ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            right: 13px;
            bottom: 5px;
            left: 13px;
            height: 2px;
            border-radius: 999px;
            background: #7dd3fc;
            opacity: 0;
            transform: scaleX(.35);
            transition: opacity 160ms ease, transform 160ms ease;
        }

        .nav-links a:hover,
        .nav-links a:focus-visible,
        .nav-links a.is-active {
            color: #fff;
            background: rgba(255, 255, 255, 0.09);
        }

        .nav-links a:hover::after,
        .nav-links a:focus-visible::after,
        .nav-links a.is-active::after {
            opacity: 1;
            transform: scaleX(1);
        }

        .nav-cta {
            min-width: 116px;
            min-height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 14px 10px 17px;
            color: var(--primary-dark);
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 12px;
            background: linear-gradient(145deg, #fff, #f8fbff);
            box-shadow: 0 9px 22px rgba(2, 6, 23, 0.17);
            text-decoration: none;
            font-size: 0.76rem;
            font-weight: 800;
            transition: transform 160ms ease, box-shadow 160ms ease, color 160ms ease;
        }

        .nav-cta:hover {
            color: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(2, 6, 23, 0.23);
        }

        .nav-cta svg {
            width: 23px;
            height: 23px;
            padding: 4px;
            color: var(--primary);
            border-radius: 50%;
            background: #eff6ff;
            transition: transform 160ms ease;
        }

        .nav-cta:hover svg {
            transform: translateX(2px);
        }

        .menu-toggle {
            width: 42px;
            height: 42px;
            display: none;
            place-items: center;
            padding: 0;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
        }

        .menu-toggle svg {
            width: 21px;
            height: 21px;
        }

        .menu-toggle .close-icon,
        .menu-toggle.is-open .menu-icon {
            display: none;
        }

        .menu-toggle.is-open .close-icon {
            display: block;
        }

        .hero {
            position: relative;
            min-height: 780px;
            display: flex;
            align-items: center;
            overflow: hidden;
            color: #fff;
            background: #0f172a;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(4, 12, 29, 0.96) 0%, rgba(6, 20, 46, 0.86) 43%, rgba(9, 24, 54, 0.4) 74%, rgba(4, 12, 29, 0.58) 100%),
                linear-gradient(0deg, rgba(3, 10, 24, 0.8) 0%, transparent 45%),
                url('{{ asset("img/energy illustration.jpg") }}') center / cover no-repeat;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 72% 34%, rgba(14, 165, 233, 0.18), transparent 28%),
                linear-gradient(180deg, transparent 75%, rgba(15, 23, 42, 0.68));
        }

        .hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.72fr);
            align-items: center;
            gap: clamp(48px, 6vw, 76px);
            padding-top: 122px;
            padding-bottom: 118px;
        }

        .hero-copy {
            max-width: 690px;
        }

        .hero-badge {
            width: max-content;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
            padding: 8px 12px;
            color: #dbeafe;
            border: 1px solid rgba(147, 197, 253, 0.25);
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.16);
            backdrop-filter: blur(10px);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .hero-badge span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #7dd3fc;
            box-shadow: 0 0 0 5px rgba(125, 211, 252, 0.12);
        }

        .hero h1 {
            margin: 0;
            color: #fff;
            font-size: clamp(2.7rem, 4.45vw, 4.35rem);
            line-height: 1.06;
            letter-spacing: -0.052em;
            text-wrap: balance;
        }

        .hero h1 em {
            color: #7dd3fc;
            font-style: normal;
        }

        .hero-lead {
            max-width: 630px;
            margin: 24px 0 0;
            color: rgba(226, 232, 240, 0.84);
            font-size: clamp(0.98rem, 1.5vw, 1.1rem);
            line-height: 1.75;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .button {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 20px;
            border: 1px solid transparent;
            border-radius: 13px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 800;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .button svg {
            width: 18px;
            height: 18px;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-primary {
            color: #fff;
            background: linear-gradient(110deg, var(--primary), var(--indigo), var(--sky));
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.27);
        }

        .button-primary:hover {
            box-shadow: 0 19px 38px rgba(37, 99, 235, 0.36);
        }

        .button-ghost {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }

        .button-ghost:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .hero-note {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 22px 0 0;
            color: rgba(203, 213, 225, 0.7);
            font-size: 0.68rem;
        }

        .hero-note svg {
            width: 16px;
            height: 16px;
            color: #86efac;
        }

        .dashboard-preview {
            position: relative;
            padding: 17px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 28px 65px rgba(2, 6, 23, 0.4);
            backdrop-filter: blur(18px);
            transform: none;
        }

        .preview-inner {
            overflow: hidden;
            padding: 19px;
            color: var(--ink);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.95);
        }

        .preview-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .preview-title strong,
        .preview-title span {
            display: block;
        }

        .preview-title strong {
            color: #1e293b;
            font-size: 0.76rem;
        }

        .preview-title span {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 0.58rem;
        }

        .live-pill {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            color: #166534;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            background: #f0fdf4;
            font-size: 0.55rem;
            font-weight: 800;
        }

        .live-pill::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #22c55e;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .metric {
            padding: 12px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            background: #fff;
        }

        .metric span,
        .metric strong,
        .metric small {
            display: block;
        }

        .metric span {
            color: #94a3b8;
            font-size: 0.5rem;
        }

        .metric strong {
            margin-top: 7px;
            color: #1e293b;
            font-size: 0.82rem;
        }

        .metric small {
            margin-top: 3px;
            color: #16a34a;
            font-size: 0.48rem;
            font-weight: 700;
        }

        .chart-card {
            margin-top: 10px;
            padding: 13px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
        }

        .chart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #475569;
            font-size: 0.55rem;
            font-weight: 700;
        }

        .chart-head span:last-child {
            color: #94a3b8;
            font-weight: 500;
        }

        .chart {
            height: 105px;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-bottom: 1px solid #e2e8f0;
            background: repeating-linear-gradient(to bottom, #f1f5f9 0 1px, transparent 1px 30px);
        }

        .bar {
            flex: 1;
            min-width: 9px;
            border-radius: 5px 5px 0 0;
            background: linear-gradient(180deg, #60a5fa, #2563eb);
            box-shadow: 0 5px 12px rgba(37, 99, 235, 0.16);
        }

        .floating-chip {
            position: absolute;
            right: -12px;
            bottom: -22px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 13px;
            color: #334155;
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 16px 30px rgba(2, 6, 23, 0.25);
            font-size: 0.58rem;
            font-weight: 700;
        }

        .floating-chip span {
            width: 26px;
            height: 26px;
            display: grid;
            place-items: center;
            color: #15803d;
            border-radius: 8px;
            background: #dcfce7;
        }

        .floating-chip svg {
            width: 15px;
            height: 15px;
        }

        .capability-strip {
            position: relative;
            z-index: 3;
            margin-top: -52px;
        }

        .capability-inner {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 9px;
            padding: 10px;
            overflow: visible;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .capability {
            position: relative;
            overflow: hidden;
            min-height: 82px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 17px;
            border: 1px solid #e5edf7;
            border-radius: 14px;
            background: linear-gradient(145deg, #f8fbff, #fff);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .capability::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #60a5fa, transparent);
            opacity: 0;
            transition: opacity 180ms ease;
        }

        .capability::after {
            content: attr(data-index);
            position: absolute;
            top: 9px;
            right: 11px;
            color: #dbe4f0;
            font-size: 0.55rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .capability:hover {
            z-index: 1;
            transform: translateY(-4px);
            border-color: #bfdbfe;
            box-shadow: 0 13px 26px rgba(37, 99, 235, 0.11);
        }

        .capability:hover::before {
            opacity: 1;
        }

        .capability-icon {
            width: 43px;
            height: 43px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            color: var(--primary);
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: linear-gradient(145deg, #eff6ff, #dbeafe);
            box-shadow: 0 7px 16px rgba(37, 99, 235, 0.09);
        }

        .capability:nth-child(2) .capability-icon {
            color: #4f46e5;
            border-color: #ddd6fe;
            background: linear-gradient(145deg, #f5f3ff, #ede9fe);
        }

        .capability:nth-child(3) .capability-icon {
            color: #0284c7;
            border-color: #bae6fd;
            background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
        }

        .capability:nth-child(4) .capability-icon {
            color: #15803d;
            border-color: #bbf7d0;
            background: linear-gradient(145deg, #f0fdf4, #dcfce7);
        }

        .capability-icon svg {
            width: 19px;
            height: 19px;
        }

        .capability > div strong,
        .capability > div span {
            display: block;
        }

        .capability strong {
            color: #1e293b;
            font-size: 0.72rem;
            letter-spacing: -0.01em;
        }

        .capability > div span {
            margin-top: 3px;
            color: #7c8ba1;
            font-size: 0.59rem;
            line-height: 1.4;
        }

        .section {
            padding: 92px 0;
        }

        .section-soft {
            position: relative;
            overflow: hidden;
            color: #fff;
            background:
                radial-gradient(circle at 12% 12%, rgba(37, 99, 235, 0.3), transparent 29%),
                radial-gradient(circle at 88% 82%, rgba(99, 102, 241, 0.2), transparent 28%),
                #0f172a;
        }

        .section-soft::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .section-heading {
            max-width: 720px;
            margin-bottom: 48px;
        }

        .section-heading.center {
            margin-right: auto;
            margin-left: auto;
            text-align: center;
        }

        .feature-heading {
            max-width: none;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(330px, 0.72fr);
            align-items: end;
            gap: 70px;
            margin-bottom: 38px;
        }

        .feature-heading > div {
            max-width: 680px;
        }

        .feature-heading > p:last-child {
            margin: 0 0 4px;
            padding-left: 24px;
            border-left: 2px solid #bfdbfe;
        }

        .section-kicker {
            margin: 0 0 12px;
            color: var(--primary);
            font-size: 0.69rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .section-heading h2 {
            margin: 0;
            color: var(--ink);
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.12;
            letter-spacing: -0.05em;
            text-wrap: balance;
        }

        .section-heading > p:last-child {
            margin: 17px 0 0;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.75;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .feature-card {
            position: relative;
            overflow: hidden;
            min-height: 245px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            grid-template-rows: auto 1fr auto;
            column-gap: 20px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
            transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            right: -55px;
            bottom: -65px;
            width: 145px;
            height: 145px;
            border-radius: 50%;
            background: #eff6ff;
            transition: transform 220ms ease;
        }

        .feature-card:nth-child(1) {
            border-color: #bfdbfe;
            background: linear-gradient(135deg, #eff6ff 0%, #fff 56%);
        }

        .feature-card:nth-child(2) {
            background: linear-gradient(135deg, #f8fafc 0%, #fff 58%);
        }

        .feature-card:nth-child(3) {
            background: linear-gradient(135deg, #f0f9ff 0%, #fff 58%);
        }

        .feature-card:nth-child(4) {
            background: linear-gradient(135deg, #eef2ff 0%, #fff 58%);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: #bfdbfe;
            box-shadow: 0 20px 42px rgba(37, 99, 235, 0.1);
        }

        .feature-card:hover::after {
            transform: scale(1.18);
        }

        .feature-icon {
            position: relative;
            z-index: 1;
            grid-row: 1 / span 2;
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            color: var(--primary);
            border: 1px solid #dbeafe;
            border-radius: 15px;
            background: #eff6ff;
        }

        .feature-icon svg {
            width: 23px;
            height: 23px;
        }

        .feature-card h3 {
            position: relative;
            z-index: 1;
            margin: 3px 0 8px;
            color: #1e293b;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .feature-card p {
            position: relative;
            z-index: 1;
            margin: 0;
            color: var(--muted);
            font-size: 0.76rem;
            line-height: 1.7;
        }

        .feature-body {
            min-width: 0;
        }

        .feature-tags {
            position: relative;
            z-index: 1;
            grid-column: 2;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 18px;
        }

        .feature-tags span {
            padding: 6px 9px;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: rgba(255,255,255,.8);
            font-size: 0.58rem;
            font-weight: 700;
        }

        .feature-link {
            position: relative;
            z-index: 2;
            grid-column: 2;
            width: max-content;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 18px;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.68rem;
            font-weight: 800;
        }

        .feature-link svg {
            width: 14px;
            height: 14px;
            transition: transform 160ms ease;
        }

        .feature-link:hover svg {
            transform: translateX(3px);
        }

        .workflow-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .workflow-step {
            position: relative;
            min-height: 250px;
            padding: 28px;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 18px;
            background: rgba(255,255,255,.065);
            backdrop-filter: blur(12px);
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
        }

        .workflow-step:hover {
            transform: translateY(-5px);
            border-color: rgba(125,211,252,.34);
            background: rgba(255,255,255,.095);
        }

        .step-number {
            width: 35px;
            height: 35px;
            display: grid;
            place-items: center;
            margin-bottom: 23px;
            color: #dbeafe;
            border: 1px solid rgba(147,197,253,.28);
            border-radius: 10px;
            background: rgba(37,99,235,.25);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
            font-size: 0.7rem;
            font-weight: 800;
        }

        .workflow-step h3 {
            margin: 0 0 10px;
            color: #fff;
            font-size: 1rem;
        }

        .workflow-step p {
            margin: 0;
            color: rgba(203,213,225,.72);
            font-size: 0.77rem;
            line-height: 1.7;
        }

        .section-soft .section-heading {
            position: relative;
            z-index: 1;
        }

        .section-soft .section-kicker {
            color: #7dd3fc;
        }

        .section-soft .section-heading h2 {
            color: #fff;
        }

        .section-soft .section-heading > p:last-child {
            color: rgba(203,213,225,.72);
        }

        .cta-wrap {
            padding: 84px 0;
            background: #fff;
        }

        .cta-card {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 36px;
            padding: 46px 52px;
            color: #fff;
            border-radius: 24px;
            background:
                radial-gradient(circle at 85% 20%, rgba(125, 211, 252, 0.24), transparent 30%),
                linear-gradient(120deg, #1e3a8a, #2563eb 52%, #4f46e5);
            box-shadow: 0 25px 55px rgba(37, 99, 235, 0.2);
        }

        .cta-card::after {
            content: '';
            position: absolute;
            right: 14%;
            bottom: -90px;
            width: 230px;
            height: 230px;
            border: 36px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .cta-copy {
            position: relative;
            z-index: 1;
            max-width: 670px;
        }

        .cta-copy h2 {
            margin: 0;
            font-size: clamp(1.55rem, 3vw, 2.3rem);
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .cta-copy p {
            margin: 12px 0 0;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.82rem;
            line-height: 1.65;
        }

        .cta-card .button {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            color: var(--primary-dark);
            background: #fff;
            box-shadow: 0 12px 26px rgba(2, 6, 23, 0.18);
        }

        .contact-section {
            padding: 112px 0;
            background: #fff;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: minmax(280px, 0.78fr) minmax(500px, 1.22fr);
            align-items: start;
            gap: clamp(50px, 8vw, 100px);
        }

        .contact-copy {
            position: sticky;
            top: 106px;
        }

        .contact-copy h2 {
            margin: 0;
            color: var(--ink);
            font-size: clamp(2rem, 4vw, 3.1rem);
            line-height: 1.12;
            letter-spacing: -0.05em;
        }

        .contact-copy > p {
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.75;
        }

        .contact-points {
            display: grid;
            gap: 18px;
            margin: 32px 0 0;
            padding: 0;
            list-style: none;
        }

        .contact-points li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            color: #475569;
            font-size: 0.76rem;
            line-height: 1.55;
        }

        .contact-points span {
            width: 33px;
            height: 33px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            color: var(--primary);
            border-radius: 10px;
            background: #eff6ff;
        }

        .contact-points svg {
            width: 17px;
            height: 17px;
        }

        .contact-form-wrap {
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.07);
        }

        .form-alert {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid;
            border-radius: 11px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .form-alert.success {
            color: #166534;
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .form-alert.warning {
            color: #92400e;
            border-color: #fde68a;
            background: #fffbeb;
        }

        .form-alert.error {
            color: #9f1239;
            border-color: #fecdd3;
            background: #fff1f2;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 17px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            display: block;
            margin: 0 0 8px 2px;
            color: #334155;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .field input,
        .field textarea {
            width: 100%;
            padding: 12px 14px;
            color: var(--ink);
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 0.8rem;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .field input {
            min-height: 48px;
        }

        .field textarea {
            min-height: 128px;
            resize: vertical;
            line-height: 1.55;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: #a3afc2;
        }

        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .field.has-error input,
        .field.has-error textarea {
            border-color: #fb7185;
        }

        .field-help,
        .field-error {
            margin: 6px 0 0 2px;
            font-size: 0.64rem;
            line-height: 1.45;
        }

        .field-help {
            color: #94a3b8;
        }

        .field-error {
            color: #be123c;
        }

        .submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-top: 21px;
        }

        .submit-row p {
            max-width: 300px;
            margin: 0;
            color: #94a3b8;
            font-size: 0.63rem;
            line-height: 1.5;
        }

        .submit-button {
            min-height: 47px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            color: #fff;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(110deg, var(--primary), var(--indigo), var(--sky));
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
            cursor: pointer;
            font-size: 0.74rem;
            font-weight: 800;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .submit-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.27);
        }

        .submit-button:disabled {
            opacity: 0.72;
            cursor: wait;
        }

        .submit-button svg {
            width: 17px;
            height: 17px;
        }

        .site-footer {
            color: rgba(255, 255, 255, 0.72);
            background: #0f172a;
        }

        .footer-main {
            display: grid;
            grid-template-columns: minmax(240px, 1.4fr) repeat(2, minmax(130px, 0.5fr));
            gap: 50px;
            padding-top: 60px;
            padding-bottom: 45px;
        }

        .footer-brand .brand {
            width: max-content;
        }

        .footer-brand p {
            max-width: 470px;
            margin: 18px 0 0;
            color: rgba(203, 213, 225, 0.62);
            font-size: 0.72rem;
            line-height: 1.7;
        }

        .footer-column h3 {
            margin: 0 0 16px;
            color: #fff;
            font-size: 0.73rem;
        }

        .footer-column nav {
            display: grid;
            gap: 11px;
        }

        .footer-column a {
            width: max-content;
            color: rgba(203, 213, 225, 0.62);
            text-decoration: none;
            font-size: 0.68rem;
            transition: color 160ms ease;
        }

        .footer-column a:hover {
            color: #fff;
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-top: 18px;
            padding-bottom: 22px;
            color: rgba(148, 163, 184, 0.68);
            border-top: 1px solid rgba(255, 255, 255, 0.09);
            font-size: 0.63rem;
        }

        html.dark-mode {
            --ink: #f1f5f9;
            --body: #cbd5e1;
            --muted: #94a3b8;
            --line: #334155;
            --soft: #111827;
        }

        html.dark-mode body { background: #0b1220; }
        html.dark-mode .site-header.is-scrolled { background: rgba(8, 15, 29, 0.96); }
        html.dark-mode .capability-inner { border-color: #334155; background: rgba(15, 23, 42, 0.93); box-shadow: 0 22px 55px rgba(0, 0, 0, 0.28); }
        html.dark-mode .capability { border-color: #334155; background: linear-gradient(145deg, #111c30, #172033); }
        html.dark-mode .capability:hover { border-color: #475b75; }
        html.dark-mode .capability strong { color: #e2e8f0; }
        html.dark-mode .capability > div span { color: #94a3b8; }
        html.dark-mode .capability::after { color: #475569; }

        html.dark-mode .feature-card,
        html.dark-mode .feature-card:nth-child(1),
        html.dark-mode .feature-card:nth-child(2),
        html.dark-mode .feature-card:nth-child(3),
        html.dark-mode .feature-card:nth-child(4) {
            border-color: #334155;
            background: linear-gradient(145deg, #111c30, #172033);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }
        html.dark-mode .feature-card:hover { border-color: #475b75; }
        html.dark-mode .feature-card::after { background: rgba(37, 99, 235, 0.08); }
        html.dark-mode .feature-card h3 { color: #f1f5f9; }
        html.dark-mode .feature-card p { color: #94a3b8; }
        html.dark-mode .feature-link { color: #7dd3fc; }

        html.dark-mode .cta-wrap,
        html.dark-mode .contact-section { background: #0b1220; }
        html.dark-mode .contact-copy h2 { color: #f1f5f9; }
        html.dark-mode .contact-copy > p,
        html.dark-mode .contact-points li { color: #94a3b8; }
        html.dark-mode .contact-points span { color: #7dd3fc; background: rgba(37, 99, 235, 0.14); }
        html.dark-mode .contact-form-wrap { border-color: #334155; background: #111c30; box-shadow: 0 18px 50px rgba(0, 0, 0, 0.22); }
        html.dark-mode .field label { color: #cbd5e1; }
        html.dark-mode .field input,
        html.dark-mode .field textarea { color: #e5edf7; border-color: #334155; background: #0f172a; }
        html.dark-mode .field input:focus,
        html.dark-mode .field textarea:focus { border-color: #3b82f6; background: #111827; }
        html.dark-mode .form-alert.success { color: #a7f3d0; border-color: #176b54; background: rgba(5,150,105,.12); }
        html.dark-mode .form-alert.warning { color: #fde68a; border-color: #72551a; background: rgba(217,119,6,.1); }
        html.dark-mode .form-alert.error { color: #fecdd3; border-color: #7f3045; background: rgba(190,18,60,.11); }

        html.dark-mode .preview-inner { color: #e2e8f0; background: rgba(15, 23, 42, 0.96); }
        html.dark-mode .preview-title strong,
        html.dark-mode .metric strong { color: #e2e8f0; }
        html.dark-mode .metric,
        html.dark-mode .chart-card { border-color: #334155; background: #111827; }
        html.dark-mode .chart-head { color: #cbd5e1; }
        html.dark-mode .chart { border-bottom-color: #334155; background: repeating-linear-gradient(to bottom, #263449 0 1px, transparent 1px 30px); }
        html.dark-mode .floating-chip { color: #cbd5e1; border-color: #475569; background: rgba(15, 23, 42, 0.95); }

        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 600ms ease, transform 600ms ease;
        }

        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .button:focus-visible,
        .nav-cta:focus-visible,
        .menu-toggle:focus-visible,
        .theme-toggle:focus-visible,
        .submit-button:focus-visible,
        .footer-column a:focus-visible,
        .feature-link:focus-visible {
            outline: 3px solid rgba(14, 165, 233, 0.45);
            outline-offset: 3px;
        }

        @media (max-width: 1020px) {
            .hero-grid {
                grid-template-columns: minmax(0, 1fr) minmax(300px, 0.68fr);
                gap: 44px;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .capability-inner {
                grid-template-columns: repeat(2, 1fr);
            }

            .capability:nth-child(2) {
                border-right: 0;
            }

            .capability:nth-child(-n+2) {
                border-bottom-color: #e5edf7;
            }
        }

        @media (max-width: 840px) {
            .site-header,
            .site-header.is-scrolled {
                height: 68px;
            }

            .menu-toggle {
                display: grid;
            }

            .nav-area {
                position: fixed;
                top: 68px;
                right: 0;
                left: 0;
                max-height: 0;
                display: grid;
                gap: 14px;
                overflow: hidden;
                padding: 0 20px;
                visibility: hidden;
                background: rgba(15, 23, 42, 0.98);
                box-shadow: 0 18px 35px rgba(2, 6, 23, 0.22);
                opacity: 0;
                transition: max-height 220ms ease, padding 220ms ease, opacity 180ms ease, visibility 180ms ease;
            }

            .nav-area.is-open {
                max-height: 410px;
                padding-top: 18px;
                padding-bottom: 22px;
                visibility: visible;
                opacity: 1;
            }

            .nav-links {
                align-items: stretch;
                flex-direction: column;
            }

            .nav-links a {
                padding: 11px 12px;
            }

            .nav-cta {
                width: 100%;
            }

            .theme-toggle {
                width: 100%;
            }

            .hero {
                min-height: auto;
            }

            .hero-grid {
                grid-template-columns: 1fr;
                padding-top: 132px;
                padding-bottom: 120px;
            }

            .hero-copy {
                max-width: 720px;
            }

            .dashboard-preview {
                width: min(100%, 520px);
                margin-inline: auto;
                transform: none;
            }

            .workflow-grid {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .feature-heading {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .feature-heading > p:last-child {
                max-width: 680px;
                padding-left: 18px;
            }

            .workflow-step:not(:last-child)::after {
                display: none;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-copy {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: min(calc(100% - 28px), var(--container));
            }

            .brand span {
                max-width: 210px;
                font-size: 0.82rem;
            }

            .brand img {
                width: 39px;
                height: 39px;
            }

            .hero::before {
                background:
                    linear-gradient(90deg, rgba(4, 12, 29, 0.94), rgba(6, 20, 46, 0.74)),
                    linear-gradient(0deg, rgba(3, 10, 24, 0.78), transparent 45%),
                    url('{{ asset("img/energy illustration.jpg") }}') center / cover no-repeat;
            }

            .hero-grid {
                padding-top: 116px;
                padding-bottom: 96px;
            }

            .hero h1 {
                font-size: clamp(2.35rem, 12vw, 3.3rem);
            }

            .hero-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .hero-actions .button {
                width: 100%;
            }

            .floating-chip {
                right: 10px;
            }

            .capability-strip {
                margin-top: -34px;
            }

            .capability-inner,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .capability {
                border-right: 0;
                border-bottom: 1px solid #e5edf7;
            }

            .capability:last-child {
                border-bottom: 0;
            }

            .section,
            .contact-section {
                padding: 82px 0;
            }

            .section-heading {
                margin-bottom: 34px;
            }

            .feature-card {
                min-height: 0;
                grid-template-columns: 1fr;
                grid-template-rows: auto;
                padding: 24px 21px;
            }

            .feature-icon,
            .feature-tags,
            .feature-link {
                grid-column: 1;
            }

            .feature-icon {
                grid-row: auto;
                margin-bottom: 18px;
            }

            .feature-card h3 {
                margin-top: 0;
            }

            .cta-wrap {
                padding-bottom: 82px;
            }

            .cta-card {
                align-items: flex-start;
                flex-direction: column;
                padding: 34px 25px;
            }

            .cta-card .button {
                width: 100%;
            }

            .contact-form-wrap {
                padding: 22px 17px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .submit-row {
                align-items: stretch;
                flex-direction: column;
            }

            .submit-button {
                width: 100%;
            }

            .footer-main {
                grid-template-columns: 1fr 1fr;
                gap: 36px 24px;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }

            .footer-bottom {
                align-items: flex-start;
                flex-direction: column;
                gap: 7px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }

            .reveal {
                opacity: 1;
                transform: none;
            }
        }
    </style>
</head>
<body>
    @php
        $contactUser = auth()->user();
        $prefillName = old('name', $contactUser?->full_name ?? $contactUser?->name ?? $contactUser?->username ?? '');
        $prefillEmail = old('email', $contactUser?->email ?? '');
    @endphp

    <header class="site-header" id="siteHeader">
        <div class="container nav-wrap">
            <a class="brand" href="{{ url('/') }}" aria-label="{{ $systemName }} home">
                <img src="{{ $systemLogoUrl }}" alt="">
                <span>{{ $systemName }}</span>
            </a>

            <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="navArea">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
                <svg class="close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="nav-area" id="navArea">
                <nav class="nav-links" aria-label="Main navigation">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="{{ route('about.index') }}">About</a>
                    <a href="#contact">Contact</a>
                </nav>
                <a class="nav-cta" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                    {{ auth()->check() ? 'Open dashboard' : 'Sign in' }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <button class="theme-toggle" id="landingThemeToggle" type="button" aria-label="Switch to dark mode" title="Toggle theme">
                    <svg class="moon-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.7 15.2A8.7 8.7 0 0 1 8.8 3.3 9 9 0 1 0 20.7 15.2Z"/></svg>
                    <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>
    </header>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="hero-badge"><span aria-hidden="true"></span>Local government energy intelligence</div>
                    <h1 id="hero-title">Smarter energy decisions for <em>better public service.</em></h1>
                    <p class="hero-lead">
                        Bring facility records, energy monitoring, and actionable reports together in one secure platform built for accountable local governance.
                    </p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                            {{ auth()->check() ? 'Go to dashboard' : 'Access the system' }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <a class="button button-ghost" href="#features">
                            Explore capabilities
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                    <p class="hero-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Secure, role-based access for authorized personnel
                    </p>
                </div>

                <div class="dashboard-preview" aria-label="Energy dashboard preview">
                    <div class="preview-inner">
                        <div class="preview-head">
                            <div class="preview-title">
                                <strong>Interface preview</strong>
                                <span>Illustrative performance snapshot</span>
                            </div>
                            <span class="live-pill">Sample</span>
                        </div>
                        <div class="metric-grid">
                            <div class="metric"><span>Consumption</span><strong>24.8k</strong><small>kWh tracked</small></div>
                            <div class="metric"><span>Efficiency</span><strong>86%</strong><small>On target</small></div>
                            <div class="metric"><span>Facilities</span><strong>12</strong><small>Connected</small></div>
                        </div>
                        <div class="chart-card">
                            <div class="chart-head"><span>Monthly consumption</span><span>Last 7 periods</span></div>
                            <div class="chart" aria-hidden="true">
                                <span class="bar" style="height:46%"></span>
                                <span class="bar" style="height:62%"></span>
                                <span class="bar" style="height:54%"></span>
                                <span class="bar" style="height:78%"></span>
                                <span class="bar" style="height:69%"></span>
                                <span class="bar" style="height:88%"></span>
                                <span class="bar" style="height:73%"></span>
                            </div>
                        </div>
                    </div>
                    <div class="floating-chip">
                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        Structured review workflow
                    </div>
                </div>
            </div>
        </section>

        <section class="capability-strip" aria-label="Platform capabilities">
            <div class="container capability-inner">
                <div class="capability" data-index="01">
                    <span class="capability-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m13 2-8 12h7l-1 8 8-12h-7l1-8Z" stroke-linejoin="round"/></svg></span>
                    <div><strong>Energy visibility</strong><span>Track usage records</span></div>
                </div>
                <div class="capability" data-index="02">
                    <span class="capability-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V8l8-5v18M12 10h8v11M8 8v1M8 12v1M8 16v1M16 14v1M16 18v1" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div><strong>Facility oversight</strong><span>Organize public assets</span></div>
                </div>
                <div class="capability" data-index="03">
                    <span class="capability-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2" stroke-linecap="round"/></svg></span>
                    <div><strong>Decision-ready reports</strong><span>Turn records into insight</span></div>
                </div>
                <div class="capability" data-index="04">
                    <span class="capability-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div><strong>Secure access</strong><span>Role-based permissions</span></div>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <div class="section-heading feature-heading reveal">
                    <div>
                        <p class="section-kicker">Core capabilities</p>
                        <h2>One platform for clearer energy operations.</h2>
                    </div>
                    <p>Give teams the visibility and structure they need to maintain accurate records, identify patterns, and act with confidence.</p>
                </div>

                <div class="features-grid">
                    <article class="feature-card reveal">
                        <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m13 2-8 12h7l-1 8 8-12h-7l1-8Z" stroke-linejoin="round"/></svg></span>
                        <h3>Energy monitoring</h3>
                        <p>Capture and review energy records across facilities with a clear view of consumption trends.</p>
                        <div class="feature-tags"><span>Usage records</span><span>Consumption trends</span></div>
                        <a class="feature-link" href="{{ route('landing.features') }}">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </article>
                    <article class="feature-card reveal">
                        <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V8l8-5v18M12 10h8v11M8 8v1M8 12v1M8 16v1M16 14v1M16 18v1" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <h3>Facility management</h3>
                        <p>Keep facility profiles, meters, and related operational information organized in one place.</p>
                        <div class="feature-tags"><span>Facility profiles</span><span>Meter inventory</span></div>
                        <a class="feature-link" href="{{ route('landing.features') }}">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </article>
                    <article class="feature-card reveal">
                        <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2" stroke-linecap="round"/></svg></span>
                        <h3>Reports and analytics</h3>
                        <p>Transform recorded data into summaries that support planning, review, and accountability.</p>
                        <div class="feature-tags"><span>Summary reports</span><span>Performance insights</span></div>
                        <a class="feature-link" href="{{ route('landing.features') }}">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </article>
                    <article class="feature-card reveal">
                        <span class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <h3>Controlled access</h3>
                        <p>Protect operational information through verified accounts and role-appropriate permissions.</p>
                        <div class="feature-tags"><span>Verified accounts</span><span>Role permissions</span></div>
                        <a class="feature-link" href="{{ route('landing.features') }}">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-soft" id="how-it-works">
            <div class="container">
                <div class="section-heading center reveal">
                    <p class="section-kicker">From data to action</p>
                    <h2>A straightforward operational workflow.</h2>
                    <p>Keep the process simple—from maintaining facility information to reviewing performance and sharing useful reports.</p>
                </div>
                <div class="workflow-grid">
                    <article class="workflow-step reveal">
                        <span class="step-number">01</span>
                        <h3>Organize facilities</h3>
                        <p>Maintain accurate facility and meter profiles so every energy record has the right operational context.</p>
                    </article>
                    <article class="workflow-step reveal">
                        <span class="step-number">02</span>
                        <h3>Monitor performance</h3>
                        <p>Review consumption records, changes, and relevant indicators through a centralized monitoring workspace.</p>
                    </article>
                    <article class="workflow-step reveal">
                        <span class="step-number">03</span>
                        <h3>Report and improve</h3>
                        <p>Use structured summaries and insights to support planning, operational review, and conservation efforts.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta-wrap">
            <div class="container">
                <div class="cta-card reveal">
                    <div class="cta-copy">
                        <h2>Ready to manage energy information with greater clarity?</h2>
                        <p>Authorized team members can securely access the workspace and continue monitoring local government facilities.</p>
                    </div>
                    <a class="button" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                        {{ auth()->check() ? 'Open dashboard' : 'Sign in securely' }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <section class="contact-section" id="contact">
            <div class="container contact-grid">
                <div class="contact-copy reveal">
                    <p class="section-kicker">Contact and support</p>
                    <h2>How can we help?</h2>
                    <p>Send your question, concern, or support request. Provide enough context so the responsible team can assist you effectively.</p>
                    <ul class="contact-points">
                        <li><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5.5h16v13H4z"/><path d="m5 7 7 5 7-5" stroke-linejoin="round"/></svg></span>Use an active email address where you can receive a response.</li>
                        <li><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 10.5V16M12 7.5h.01" stroke-linecap="round"/></svg></span>Include the facility or module involved when reporting a system concern.</li>
                        <li><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Never include passwords, OTP codes, or other sensitive credentials.</li>
                    </ul>
                </div>

                <div class="contact-form-wrap reveal">
                    @if (session('contact_success'))
                        <div class="form-alert success" role="status">{{ session('contact_success') }}</div>
                    @endif
                    @if (session('contact_warning'))
                        <div class="form-alert warning" role="alert">{{ session('contact_warning') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="form-alert error" role="alert">Please review the highlighted fields and try again.</div>
                    @endif

                    <form id="contactForm" method="POST" action="{{ route('landing.contact.store') }}">
                        @csrf
                        <div class="form-grid">
                            <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="contactName">Full name</label>
                                <input id="contactName" type="text" name="name" value="{{ $prefillName }}" placeholder="Juan Dela Cruz" autocomplete="name" required>
                                <p class="field-help">Use your personal name, not an office name.</p>
                                @error('name')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                                <label for="contactEmail">Email address</label>
                                <input id="contactEmail" type="email" name="email" value="{{ $prefillEmail }}" placeholder="name@example.com" autocomplete="email" required>
                                @error('email')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field full {{ $errors->has('subject') ? 'has-error' : '' }}">
                                <label for="contactSubject">Subject <span style="color:#94a3b8;font-weight:500">(optional)</span></label>
                                <input id="contactSubject" type="text" name="subject" value="{{ old('subject') }}" placeholder="What is your message about?">
                                @error('subject')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field full {{ $errors->has('message') ? 'has-error' : '' }}">
                                <label for="contactMessage">Message</label>
                                <textarea id="contactMessage" name="message" placeholder="Tell us how we can help..." required>{{ old('message') }}</textarea>
                                @error('message')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="submit-row">
                            <p>Your message will be routed to the appropriate system team for review.</p>
                            <button class="submit-button" id="contactSubmit" type="submit">
                                <span id="contactSubmitLabel">Send message</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 14-7-4 14-3-6-7-1Z" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-main">
            <div class="footer-brand">
                <a class="brand" href="{{ url('/') }}">
                    <img src="{{ $systemLogoUrl }}" alt="">
                    <span>{{ $systemName }}</span>
                </a>
                <p>A secure local government platform for organizing facility information, monitoring energy records, and supporting better operational decisions.</p>
            </div>
            <div class="footer-column">
                <h3>Platform</h3>
                <nav>
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Dashboard' : 'Sign in' }}</a>
                </nav>
            </div>
            <div class="footer-column">
                <h3>Information</h3>
                <nav>
                    <a href="{{ route('about.index') }}">About</a>
                    <a href="{{ route('faqs.index') }}">FAQs</a>
                    <a href="{{ route('privacy.index') }}">Privacy notice</a>
                    <a href="#contact">Contact</a>
                </nav>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; {{ date('Y') }} {{ $systemName }}. All rights reserved.</span>
            <span>{{ $systemOrganization }}</span>
        </div>
    </footer>

    <script>
        (function () {
            const header = document.getElementById('siteHeader');
            const themeToggle = document.getElementById('landingThemeToggle');
            const menuToggle = document.getElementById('menuToggle');
            const navArea = document.getElementById('navArea');
            const contactForm = document.getElementById('contactForm');
            const contactSubmit = document.getElementById('contactSubmit');
            const contactSubmitLabel = document.getElementById('contactSubmitLabel');
            const sectionLinks = Array.from(document.querySelectorAll('.nav-links a[href^="#"]'));

            function applyTheme(isDark) {
                document.documentElement.classList.toggle('dark-mode', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
                if (themeToggle) themeToggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                try { localStorage.setItem('darkMode', isDark ? 'on' : 'off'); } catch (e) {}
            }

            applyTheme(document.documentElement.classList.contains('dark-mode'));

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    applyTheme(!document.documentElement.classList.contains('dark-mode'));
                });
            }

            function updateHeader() {
                if (header) header.classList.toggle('is-scrolled', window.scrollY > 24);
            }

            function closeMenu() {
                if (!menuToggle || !navArea) return;
                menuToggle.classList.remove('is-open');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Open navigation');
                navArea.classList.remove('is-open');
                document.body.classList.remove('menu-open');
            }

            updateHeader();
            window.addEventListener('scroll', updateHeader, { passive: true });

            if (menuToggle && navArea) {
                menuToggle.addEventListener('click', function () {
                    const opening = !navArea.classList.contains('is-open');
                    this.classList.toggle('is-open', opening);
                    this.setAttribute('aria-expanded', opening ? 'true' : 'false');
                    this.setAttribute('aria-label', opening ? 'Close navigation' : 'Open navigation');
                    navArea.classList.toggle('is-open', opening);
                    document.body.classList.toggle('menu-open', opening);
                });

                navArea.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', closeMenu);
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth > 840) closeMenu();
                });
            }

            if ('IntersectionObserver' in window && sectionLinks.length) {
                const sectionObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;

                        sectionLinks.forEach(function (link) {
                            const isCurrent = link.getAttribute('href') === '#' + entry.target.id;
                            link.classList.toggle('is-active', isCurrent);
                            if (isCurrent) {
                                link.setAttribute('aria-current', 'location');
                            } else {
                                link.removeAttribute('aria-current');
                            }
                        });
                    });
                }, { rootMargin: '-28% 0px -58% 0px', threshold: 0 });

                sectionLinks.forEach(function (link) {
                    const target = document.querySelector(link.getAttribute('href'));
                    if (target) sectionObserver.observe(target);
                });
            }

            const revealItems = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, { threshold: 0.12 });

                revealItems.forEach(function (item) { observer.observe(item); });
            } else {
                revealItems.forEach(function (item) { item.classList.add('is-visible'); });
            }

            if (contactForm && contactSubmit && contactSubmitLabel) {
                contactForm.addEventListener('submit', function () {
                    if (!this.checkValidity()) return;
                    contactSubmit.disabled = true;
                    contactSubmit.setAttribute('aria-busy', 'true');
                    contactSubmitLabel.textContent = 'Sending...';
                });
            }

            @if ($errors->any() || session('contact_success') || session('contact_warning'))
                window.addEventListener('load', function () {
                    document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
                });
            @endif
        })();
    </script>
</body>
</html>

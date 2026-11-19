<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Che168 — VAN2KAUTO')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c0e;
            --bg-card: #12151a;
            --border: rgba(255,255,255,.1);
            --text: #f4f6f8;
            --muted: rgba(244,246,248,.65);
            --accent: #3b9eff;
            --accent-2: #6c5ce7;
            --danger: #ef4444;
            --ok: #22c55e;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100%; font-family: Commissioner, system-ui, sans-serif;
            background: var(--bg); color: var(--text);
            background-image: radial-gradient(ellipse 80% 50% at 10% 0%, rgba(59,158,255,.12), transparent),
                radial-gradient(ellipse 60% 40% at 90% 20%, rgba(108,92,231,.1), transparent);
        }
        a { color: var(--accent); text-decoration: none; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 1rem 3rem; }
        header {
            position: sticky; top: 0; z-index: 50; backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border); background: rgba(10,12,14,.85);
        }
        .head-in { max-width: 1200px; margin: 0 auto; padding: .75rem 1rem; display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
        .logo { font-weight: 700; letter-spacing: .06em; color: var(--text); }
        .logo span { color: var(--accent); }
        nav a { color: var(--muted); font-size: .9rem; padding: .35rem .65rem; border-radius: 999px; }
        nav a:hover, nav a.active { color: var(--text); background: rgba(255,255,255,.06); }
        .btn {
            display: inline-flex; align-items: center; gap: .35rem; padding: .55rem 1rem; border-radius: 999px;
            font-weight: 600; font-size: .875rem; border: none; cursor: pointer; text-decoration: none;
        }
        .btn-accent { background: linear-gradient(135deg, var(--accent), var(--accent-2)); color: #fff; }
        .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-danger { background: var(--danger); color: #fff; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 1rem; padding: 1.25rem; }
        .grid-3 { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .stat h3 { margin: 0; font-size: 1.75rem; }
        .stat p { margin: .25rem 0 0; color: var(--muted); font-size: .85rem; }
        .alert { padding: .75rem 1rem; border-radius: .75rem; margin-bottom: 1rem; }
        .alert-warn { background: rgba(234,179,8,.15); border: 1px solid rgba(234,179,8,.4); }
        .alert-ok { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.35); }
        .log { font-family: ui-monospace, monospace; font-size: .75rem; max-height: 240px; overflow-y: auto; color: var(--muted); }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        th, td { padding: .6rem .5rem; border-bottom: 1px solid var(--border); text-align: left; }
        label { display: block; font-size: .8rem; color: var(--muted); margin-bottom: .25rem; }
        input, select, textarea { width: 100%; padding: .5rem .75rem; border-radius: .5rem; border: 1px solid var(--border); background: #0d1014; color: var(--text); }
        img { max-width: 100%; height: auto; }
        .badge { font-size: .7rem; padding: .15rem .45rem; border-radius: 4px; text-transform: uppercase; }
        .badge-active { background: rgba(34,197,94,.2); color: #86efac; }
        .badge-absent { background: rgba(239,68,68,.2); color: #fca5a5; }
        .badge-inactive { background: rgba(148,163,184,.2); color: #cbd5e1; }
        .che168-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; }
        .che168-pager-meta { margin: 0; font-size: .85rem; color: var(--muted); }
        .che168-pager-pages { display: flex; flex-wrap: wrap; gap: .35rem; list-style: none; margin: 0; padding: 0; align-items: center; }
        .che168-pager-pages a, .che168-pager-pages span {
            display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; padding: .35rem .55rem;
            border-radius: .5rem; border: 1px solid var(--border); font-size: .85rem; text-decoration: none;
        }
        .che168-pager-pages a { color: var(--text); background: var(--bg-card); }
        .che168-pager-pages a:hover { border-color: var(--accent); color: var(--accent); }
        .che168-pager-pages .is-current { background: rgba(59,158,255,.2); color: var(--text); border-color: var(--accent); font-weight: 600; }
        .che168-pager-pages .is-disabled, .che168-pager-pages .is-gap { color: var(--muted); border-color: transparent; background: transparent; }
        .filters { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); align-items: end; }
        .detail-layout { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr) 300px; align-items: start; }
        @media (max-width: 860px) { .detail-layout { grid-template-columns: 1fr; } }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .5rem; }
        .gallery button { display: block; width: 100%; }
        .gallery img { width: 100%; height: 105px; object-fit: cover; border-radius: 8px; display: block; }
        .hero-photo { width: 100%; max-height: 420px; object-fit: cover; border-radius: 12px; margin: .75rem 0; }
        .facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .5rem .75rem; margin: 1rem 0; }
        .facts div { background: rgba(255,255,255,.03); border: 1px solid var(--border); border-radius: .6rem; padding: .55rem .7rem; }
        .facts span { display: block; color: var(--muted); font-size: .75rem; }
        .spec-block { margin-top: 1.25rem; }
        .spec-block h3 { margin: 0 0 .5rem; font-size: .95rem; }
    </style>
    @stack('head')
</head>
<body class="min-h-full">
<header>
    <div class="head-in">
        @hasSection('cabinet-logo')
            @yield('cabinet-logo')
        @else
            <a href="{{ route('che168.dashboard') }}" class="logo">VAN<span>2</span>KAUTO · Che168</a>
        @endif
        @auth
        <nav>
            @hasSection('cabinet-nav')
                @yield('cabinet-nav')
            @else
            <a href="{{ route('automarket.dashboard') }}">Аукционы</a>
            <a href="{{ route('che168.dashboard') }}" class="@if(request()->routeIs('che168.dashboard')) active @endif">Парсер</a>
            <a href="{{ route('che168.listings.index') }}" class="@if(request()->routeIs('che168.listings.*')) active @endif">Объявления</a>
            <a href="{{ route('api.docs') }}" class="@if(request()->routeIs('api.docs')) active @endif">Документация</a>
            <a href="{{ route('che168.gate.public') }}" target="_blank">Captcha</a>
            @endif
        </nav>
        <form method="post" action="{{ route('che168.logout') }}" style="margin-left:auto">
            @csrf
            <button type="submit" class="btn btn-ghost">Выход</button>
        </form>
        @else
        <nav>
            <a href="{{ route('api.docs') }}" class="@if(request()->routeIs('api.docs')) active @endif">Документация</a>
        </nav>
        @endauth
    </div>
</header>
<main class="wrap" style="padding-top:1.5rem">
    @if(session('ok'))
        <div class="alert alert-ok">{{ session('ok') }}</div>
    @endif
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
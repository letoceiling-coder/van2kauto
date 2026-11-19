<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — Che168</title>
    <link href="https://fonts.googleapis.com/css2?family=Commissioner:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:Commissioner,system-ui,sans-serif;
            background:#0a0c0e; color:#f4f6f8; }
        .box { width:min(400px,92vw); padding:2rem; border:1px solid rgba(255,255,255,.1); border-radius:1rem; background:#12151a; }
        h1 { margin:0 0 1rem; font-size:1.25rem; }
        label { display:block; font-size:.85rem; color:rgba(255,255,255,.6); margin-bottom:.25rem; }
        input { width:100%; margin-bottom:1rem; padding:.6rem .75rem; border-radius:.5rem; border:1px solid rgba(255,255,255,.15); background:#0d1014; color:#fff; }
        button { width:100%; padding:.65rem; border:none; border-radius:999px; font-weight:600; cursor:pointer;
            background:linear-gradient(135deg,#3b9eff,#6c5ce7); color:#fff; }
        .err { color:#f87171; font-size:.85rem; margin-bottom:.75rem; }
    </style>
</head>
<body>
<div class="box">
    <h1>VAN<span style="color:#3b9eff">2</span>KAUTO · Che168</h1>
    @if($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('che168.login.post') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        <label>Пароль</label>
        <input type="password" name="password" required autocomplete="current-password">
        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>
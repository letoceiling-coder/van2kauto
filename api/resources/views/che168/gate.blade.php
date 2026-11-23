<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Che168 — проверка EdgeOne</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; }
        header { padding: 12px 16px; background: #1e293b; font-size: 14px; border-bottom: 1px solid #334155; }
        header strong { color: #38bdf8; }
        #status { padding: 10px 16px; min-height: 1.25rem; font-size: 14px; }
        #status.ok { background: #14532d; color: #bbf7d0; }
        #status.err { background: #7f1d1d; color: #fecaca; }
        iframe { width: 100%; height: calc(100vh - 88px); border: 0; background: #fff; }
    </style>
</head>
<body>
    <header>
        <strong>Che168 / EdgeOne</strong> — страница грузится <em>с IP сервера</em>. Отметьте галочку внизу. Токен сохранится в .env автоматически.
    </header>
    <div id="status">Ожидание captcha…</div>
    <iframe id="gate" src="{{ $proxyUrl }}" title="Che168 verification"></iframe>
    <script>
        window.addEventListener('message', function (e) {
            if (!e.data || e.data.type !== 'eo-captcha-saved') return;
            var el = document.getElementById('status');
            el.className = e.data.ok ? 'ok' : 'err';
            el.textContent = e.data.message || (e.data.ok ? 'Готово' : 'Ошибка');
        });
    </script>
</body>
</html>
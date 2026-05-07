<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: #1e293b; padding: 2.5rem; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 25px 50px rgba(0,0,0,0.4); }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center; color: #38bdf8; }
        .field { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.85rem; color: #94a3b8; }
        input[type="password"] { width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: #f1f5f9; font-size: 1rem; }
        input[type="password"]:focus { outline: none; border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,0.15); }
        button { width: 100%; padding: 0.75rem; border-radius: 8px; border: none; background: #0284c7; color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; }
        button:hover { background: #0369a1; }
        .error { background: #7f1d1d; color: #fca5a5; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Super Admin</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('super.login') }}">
            @csrf
            <div class="field">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autofocus required />
            </div>
            <button type="submit">Authenticate</button>
        </form>
    </div>
</body>
</html>

@props(['title', 'code', 'heading', 'message'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex" />
    <title>{{ $title }} — BLOMFREE & CO.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet" />
    <style>
        :root { color-scheme: light; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: #FFF8F2;
            color: #1A1A1A;
            display: grid;
            place-items: center;
            padding: 2rem;
        }
        .card {
            max-width: 36rem;
            width: 100%;
            text-align: center;
            background: #fff;
            border-radius: 1.5rem;
            padding: 3rem 2rem;
            box-shadow: 0 10px 30px rgba(26, 26, 26, 0.08);
        }
        .brand { color: #F58220; font-weight: 800; letter-spacing: -0.3px; font-size: 1.125rem; }
        .code { color: #F58220; font-weight: 800; font-size: 5rem; line-height: 1; margin: 0.5rem 0 0; letter-spacing: -2px; }
        h1 { font-size: 1.5rem; font-weight: 800; margin: 1rem 0 0.5rem; }
        p { color: #1A1A1Aaa; line-height: 1.6; margin: 0 0 1.5rem; }
        a.btn {
            display: inline-block;
            background: #F58220;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            transition: background 0.2s ease;
        }
        a.btn:hover { background: #FF6B00; }
        a.btn.ghost { background: transparent; color: #1A1A1Aaa; font-weight: 500; margin-left: 0.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">BLOMFREE &amp; CO.</div>
        <div class="code">{{ $code }}</div>
        <h1>{{ $heading }}</h1>
        <p>{{ $message }}</p>
        <a class="btn" href="/">Back to home</a>
        <a class="btn ghost" href="/contact">Contact us</a>
    </div>
</body>
</html>

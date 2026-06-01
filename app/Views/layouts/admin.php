<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? '') . ' | Admin') ?></title>
  <style>
    :root {
      --bg: #f1f5f9;
      --surface: #ffffff;
      --text: #0f172a;
      --muted: #475569;
      --line: #dbe1e8;
      --accent: #1d4ed8;
      --danger: #be123c;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      background: var(--bg);
      color: var(--text);
      font-family: "Segoe UI", Tahoma, sans-serif;
    }
    .container {
      max-width: 920px;
      margin: 0 auto;
      padding: 0 1rem;
    }
    .topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--line);
      padding: 0.9rem 0;
    }
    .topbar-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }
    .topbar a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }
    .logout-btn {
      border: 1px solid var(--line);
      background: #fff;
      color: var(--danger);
      border-radius: 0.55rem;
      padding: 0.45rem 0.75rem;
      cursor: pointer;
    }
    main { padding: 1.4rem 0 2rem; }
    .card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 0.9rem;
      padding: 1rem;
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="container topbar-row">
      <a href="<?= e(url('/admin')) ?>">Admin Panel</a>
      <?php if (is_admin_authenticated()): ?>
        <form method="post" action="<?= e(url('/admin/logout')) ?>" style="margin:0">
          <?= csrf_field() ?>
          <button class="logout-btn" type="submit">Log out</button>
        </form>
      <?php endif; ?>
    </div>
  </header>
  <main>
    <div class="container">
      <?= $content ?>
    </div>
  </main>
</body>
</html>

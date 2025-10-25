<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>404 — Under Maintenance</title>
  <meta name="robots" content="noindex">
  <style>
    :root{--bg:#0f1724;--card:#0b1220;--accent:#ff6b6b;--muted:#9aa4b2}
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial}
    body{background:linear-gradient(180deg,#071126 0%,#071b2a 100%);color:#e6eef6;display:flex;align-items:center;justify-content:center;padding:24px}

    .card{max-width:880px;width:100%;background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(255,255,255,0.01));border-radius:14px;padding:36px;display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:center;box-shadow:0 6px 30px rgba(2,6,23,0.6);border:1px solid rgba(255,255,255,0.03)}

    .left h1{font-size:48px;margin:0 0 8px;letter-spacing:-1px}
    .left p{margin:0 0 16px;color:var(--muted);line-height:1.5}
    .meta{display:flex;gap:12px;flex-wrap:wrap}
    .btn{background:transparent;border:1px solid rgba(255,255,255,0.08);padding:10px 14px;border-radius:10px;text-decoration:none;color:inherit;font-weight:600}
    .btn.primary{background:linear-gradient(90deg,var(--accent),#ff8a5b);color:#06111a;border:none}

    .right{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px}
    .status{font-weight:700;font-size:72px;color:#0b1220;background:#ffb3b3;padding:20px 28px;border-radius:14px;box-shadow:inset 0 -6px 18px rgba(0,0,0,0.08)}

    /* gear */
    .gear{width:120px;height:120px;position:relative}
    .gear svg{width:100%;height:100%;display:block}
    .gear .spin{animation:spin 6s linear infinite}
    @keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}

    .note{font-size:13px;color:var(--muted);text-align:center}

    @media (max-width:820px){
      .card{grid-template-columns:1fr;max-width:720px}
      .status{font-size:56px}
      .left h1{font-size:32px}
    }
  </style>
</head>
<body>
  <main class="card" role="main" aria-labelledby="title">
    <section class="left">
      <h1 id="title">404 — Page not found</h1>
      <p>We're currently performing scheduled maintenance. The page you requested either doesn't exist or is temporarily unavailable while we improve the service.</p>

      <div class="meta">
        <a class="btn primary" href="home.php">Return to homepage</a>
        <a class="btn" href="/status">Check system status</a>
        <a class="btn" href="mailto:support@example.com">Contact support</a>
      </div>

      <p style="margin-top:18px;color:var(--muted);font-size:14px">If you arrived here from a link, try refreshing after a few minutes. For urgent issues, contact <strong>support@example.com</strong>.</p>
    </section>

    <aside class="right" aria-hidden="true">
      <div class="status">404</div>

      <div class="gear" title="Under maintenance">
        <!-- Simple SVG gear that spins -->
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="spin" aria-hidden="true">
          <g transform="translate(50,50)">
            <path d="M0-32 L6-40 L18-36 L24-24 L33-20 L36-6 L28 2 L28 18 L36 26 L32 36 L22 40 L12 36 L4 40 L-6 36 L-12 28 L-24 24 L-30 12 L-36 6 L-32-6 L-28-18 L-32-28 L-22-36 L-10-40 L-6-32 Z" fill="#ffdede" opacity="0.12" transform="scale(0.9)"></path>
            <circle r="20" fill="#ffecec" />
            <circle r="10" fill="#0b1220" />
            <circle r="5" fill="#ffdede" />
          </g>
        </svg>
      </div>

      <div class="note">Maintenance window: <strong>Planned</strong> — expected downtime: <strong>~30 minutes</strong></div>
    </aside>
  </main>
</body>
</html>
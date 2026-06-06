<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Site Instellingen — Blueprint CMS Admin</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
<style>
  :root{--sidebar-w:240px;}
  .admin-wrap{display:flex;min-height:100vh;}
  .admin-sidebar{width:var(--sidebar-w);background:var(--surface);border-right:1px solid var(--border);
    display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:50;}
  .admin-logo{padding:1.2rem 1.5rem;font-size:1.1rem;font-weight:800;
    background:linear-gradient(135deg,#a855f7,#6c3df4);-webkit-background-clip:text;
    -webkit-text-fill-color:transparent;border-bottom:1px solid var(--border);}
  .admin-nav{padding:1rem;flex:1;}
  .admin-nav-link{display:flex;align-items:center;gap:.7rem;padding:.55rem .9rem;border-radius:8px;
    font-size:.875rem;color:var(--text-dim);margin-bottom:2px;transition:all .15s;}
  .admin-nav-link:hover,.admin-nav-link.active{background:rgba(108,61,244,.15);color:var(--accent2);}
  .admin-main{margin-left:var(--sidebar-w);flex:1;}
  .admin-topbar{height:56px;background:rgba(17,24,39,.95);border-bottom:1px solid var(--border);
    display:flex;align-items:center;padding:0 1.5rem;position:sticky;top:0;z-index:40;}
  .admin-content{padding:2rem;max-width:700px;}
</style>
</head>
<body>
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <div class="admin-logo">🔮 Blueprint CMS</div>
    <nav class="admin-nav">
      <a href="/admin" class="admin-nav-link">📊 Dashboard</a>
      <a href="/admin/blocks" class="admin-nav-link">🧩 Blokken</a>
      <a href="/admin/marketplace" class="admin-nav-link">🏪 Marketplace</a>
      <a href="/admin/settings" class="admin-nav-link active">🛠️ Instellingen</a>
      <a href="/" class="admin-nav-link">🌐 Site</a>
    </nav>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <h1 style="font-size:1rem;font-weight:700;">🛠️ Site Instellingen</h1>
    </header>
    <div class="admin-content">
      <?php if (isset($_GET['saved'])): ?>
        <div class="cf-alert cf-alert-success" style="margin-bottom:1.2rem;">✅ Instellingen opgeslagen!</div>
      <?php endif; ?>
      <div class="cf-card">
        <div class="cf-card-header">Algemeen</div>
        <div class="cf-card-body">
          <p style="color:var(--muted);font-size:.875rem;">
            Site-instellingen worden beheerd via de <a href="/config/config.php" style="color:var(--accent2)">config/config.php</a>
            of via de <a href="/installer/" style="color:var(--accent2)">installer</a>.
            Module-specifieke instellingen staan bij de modules zelf:
          </p>
          <ul style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem;font-size:.875rem;">
            <li><a href="/admin/ollama" style="color:var(--accent2)">🤖 Ollama AI instellingen</a></li>
            <li><a href="/admin/wow" style="color:var(--accent2)">🐉 World of Warcraft API</a></li>
            <li><a href="/admin/marketplace" style="color:var(--accent2)">🏪 Marketplace</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

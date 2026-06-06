<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
$online  = $status['online'] ?? false;
$players = $status['players'] ?? [];
$version = htmlspecialchars($status['version'] ?? '');
$motd    = implode(' ', $status['motd']['clean'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><title>Minecraft Server — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
</head>
<body>
<div style="max-width:800px;margin:2rem auto;padding:0 1.5rem;">
  <div class="cf-page-header">
    <h1>🟫 Minecraft Server</h1>
    <div class="cf-badge <?= $online ? 'cf-badge-green' : '' ?>" style="margin-top:.4rem;">
      <?= $online ? '🟢 Online' : '⚫ Offline' ?>
    </div>
  </div>
  <?php if ($online): ?>
    <div class="cf-card">
      <div class="cf-card-body">
        <p><strong>Versie:</strong> <?= $version ?></p>
        <p><strong>Spelers:</strong> <?= (int)($players['online'] ?? 0) ?>/<?= (int)($players['max'] ?? 0) ?></p>
        <?php if ($motd): ?><p style="color:var(--muted);font-size:.85rem;font-family:var(--font-mono)"><?= htmlspecialchars($motd) ?></p><?php endif; ?>
        <?php if (!empty($players['list'])): ?>
          <div style="margin-top:.8rem;display:flex;flex-wrap:wrap;gap:.3rem;">
            <?php foreach (array_slice($players['list'], 0, 20) as $p): ?>
              <span class="cf-badge cf-badge-purple"><?= htmlspecialchars($p['name'] ?? $p) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="cf-alert cf-alert-error">Server is momenteel offline of onbereikbaar.</div>
  <?php endif; ?>
</div>
</body>
</html>

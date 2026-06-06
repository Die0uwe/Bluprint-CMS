<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
$online = $data !== null;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><title>FiveM Server — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
</head>
<body>
<div style="max-width:800px;margin:2rem auto;padding:0 1.5rem;">
  <div class="cf-page-header">
    <h1>🚗 FiveM Server</h1>
    <div class="cf-badge" style="margin-top:.4rem;">
      <?= $online ? '🟢 Online — ' . count($players) . '/' . $maxPlayers . ' spelers' : '⚫ Offline' ?>
    </div>
  </div>
  <?php if ($online && !empty($players)): ?>
    <div class="cf-card">
      <div class="cf-card-header">Actieve spelers</div>
      <div class="cf-card-body">
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
          <?php foreach (array_slice($players, 0, 30) as $p): ?>
            <span class="cf-badge cf-badge-purple">
              <?= htmlspecialchars($p['name'] ?? 'Onbekend') ?>
              <span style="color:var(--gold);font-size:.7rem;"><?= (int)($p['ping'] ?? 0) ?>ms</span>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php elseif (!$online): ?>
    <div class="cf-alert cf-alert-error">Server is offline of de IP is niet geconfigureerd.</div>
  <?php endif; ?>
</div>
</body>
</html>

<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
// $members, $realm, $guild (beschikbaar vanuit WarcraftController::guild())
declare(strict_types=1);
$classColors = [
    'Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
    'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
    'Paladin'=>'#f48cba','Priest'=>'#d4d4d4','Rogue'=>'#fff468',
    'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a',
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Guild Roster — WoW — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
</head>
<body>
<div style="max-width:1100px;margin:2rem auto;padding:0 1.5rem;">
  <div class="cf-page-header">
    <div class="cf-breadcrumb"><a href="/">Home</a> → <a href="/wow">WoW</a> → Guild Roster</div>
    <h1>🐉 Guild Roster</h1>
    <?php if (!empty($members)): ?>
      <p style="color:var(--muted);margin-top:.3rem;"><?= count($members) ?> guild leden — Live via Blizzard API</p>
    <?php endif; ?>
  </div>

  <?php if (empty($members)): ?>
    <div class="cf-alert cf-alert-warning">
      Geen roster beschikbaar. Controleer de WoW API instellingen in
      <a href="/admin/wow">Admin → WoW</a>.
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;">
      <?php foreach ($members as $m):
        $char     = $m['character'] ?? [];
        $name     = htmlspecialchars($char['name'] ?? 'Onbekend');
        $class    = $char['playable_class']['name'] ?? '';
        $spec     = $char['active_spec']['name'] ?? '';
        $level    = (int)($char['level'] ?? 0);
        $rank     = (int)($m['rank'] ?? 9);
        $color    = htmlspecialchars($classColors[$class] ?? '#e2e8f0', ENT_QUOTES);
        $rankIcon = $rank === 0 ? '👑' : ($rank <= 2 ? '⭐' : '');
      ?>
      <div class="cf-card">
        <div class="cf-card-body">
          <div style="font-weight:700;color:<?= $color ?>;font-size:.95rem;">
            <?= $rankIcon ?><?= $name ?>
          </div>
          <?php if ($class): ?>
            <div style="font-size:.8rem;color:var(--muted);margin-top:.2rem;"><?= htmlspecialchars($spec) ?> <?= htmlspecialchars($class) ?></div>
          <?php endif; ?>
          <div style="font-size:.75rem;color:var(--gold);margin-top:.2rem;">Level <?= $level ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>

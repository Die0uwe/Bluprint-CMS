<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
// $char, $equip, $rio (beschikbaar vanuit WarcraftController::character())
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($char['name'] ?? 'Karakter') ?> — WoW — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
</head>
<body>
<div style="max-width:800px;margin:2rem auto;padding:0 1.5rem;">
  <div class="cf-breadcrumb" style="font-size:.82rem;color:var(--muted);margin-bottom:.8rem;">
    <a href="/">Home</a> → <a href="/wow">WoW</a> → <?= htmlspecialchars($char['name'] ?? 'Karakter') ?>
  </div>

  <?php if (empty($char)): ?>
    <div class="cf-alert cf-alert-warning">Karakter niet gevonden of API niet beschikbaar.</div>
  <?php else:
    $name     = htmlspecialchars($char['name'] ?? '');
    $realm    = htmlspecialchars($char['realm']['name'] ?? '');
    $class    = htmlspecialchars($char['character_class']['name'] ?? '');
    $spec     = htmlspecialchars($char['active_spec']['name'] ?? '');
    $level    = (int)($char['level'] ?? 0);
    $ilvl     = (int)($char['equipped_item_level'] ?? 0);
    $avgIlvl  = (int)($char['average_item_level'] ?? 0);
    $faction  = htmlspecialchars($char['faction']['name'] ?? '');
    $classColors = ['Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
        'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
        'Paladin'=>'#f48cba','Priest'=>'#d4d4d4','Rogue'=>'#fff468',
        'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a'];
    $classColor = htmlspecialchars($classColors[$char['character_class']['name'] ?? ''] ?? '#e2e8f0', ENT_QUOTES);
    $rioScore = 0;
    if ($rio) {
        $rioScore = (int)($rio['mythic_plus_scores_by_season'][0]['scores']['all'] ?? 0);
    }
  ?>
  <div class="cf-card" style="margin-bottom:1.5rem;">
    <div class="cf-card-body">
      <h1 style="font-size:1.8rem;font-weight:800;color:<?= $classColor ?>;margin-bottom:.3rem;"><?= $name ?></h1>
      <p style="color:var(--muted);"><?= $spec ?> <?= $class ?> — <?= $realm ?> (<?= $faction ?>)</p>
      <div style="display:flex;gap:1.5rem;margin-top:1rem;">
        <div style="text-align:center;">
          <div style="font-size:1.5rem;font-weight:800;color:var(--gold)"><?= $ilvl ?></div>
          <div style="font-size:.75rem;color:var(--muted)">Equipped iLvl</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;font-weight:800"><?= $avgIlvl ?></div>
          <div style="font-size:.75rem;color:var(--muted)">Avg iLvl</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;font-weight:800"><?= $level ?></div>
          <div style="font-size:.75rem;color:var(--muted)">Level</div>
        </div>
        <?php if ($rioScore > 0):
          $scoreColor = $rioScore >= 3000 ? '#ff8000' : ($rioScore >= 2000 ? '#a335ee' : ($rioScore >= 1000 ? '#0070dd' : '#1eff00')); ?>
        <div style="text-align:center;">
          <div style="font-size:1.5rem;font-weight:800;color:<?= htmlspecialchars($scoreColor, ENT_QUOTES) ?>"><?= $rioScore ?></div>
          <div style="font-size:.75rem;color:var(--muted)">M+ Score</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <a href="/wow" class="cf-btn cf-btn-ghost">← Terug naar WoW</a>
</div>
</body>
</html>

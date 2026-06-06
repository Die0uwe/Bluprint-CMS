<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
$classColors = ['Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
    'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
    'Paladin'=>'#f48cba','Priest'=>'#d4d4d4','Rogue'=>'#fff468',
    'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>World of Warcraft — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
<style>
  .wow-wrap { max-width:1200px; margin:2rem auto; padding:0 1.5rem; }
  .wow-hero {
    background: linear-gradient(135deg, rgba(196,30,58,.2), rgba(0,112,221,.2));
    border: 1px solid var(--border); border-radius:var(--radius-lg);
    padding:2.5rem; margin-bottom:2rem; text-align:center;
    background-image: radial-gradient(ellipse at 50% 0%, rgba(196,30,58,.3) 0%, transparent 70%);
  }
  .wow-hero h1 { font-size:2.2rem; font-weight:900;
    background:linear-gradient(135deg,#c41e3a,#f59e0b,#0070dd);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
  .wow-grid  { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
  .wow-section-title { font-size:1rem; font-weight:700; margin-bottom:1rem;
    display:flex; align-items:center; gap:.5rem; }
  @media(max-width:768px) { .wow-grid { grid-template-columns:1fr; } }
</style>
</head>
<body>
<div class="wow-wrap">
  <div class="wow-hero">
    <h1>🐉 World of Warcraft</h1>
    <?php if ($guildInfo): ?>
      <p style="color:var(--text-dim);margin-top:.5rem;font-size:1.1rem;">
        <?= htmlspecialchars($guildInfo['name'] ?? '') ?> —
        <?= htmlspecialchars($guildInfo['realm']['name'] ?? '') ?>
      </p>
    <?php endif; ?>
  </div>

  <div class="wow-grid">
    <!-- Raid Progress -->
    <div class="cf-card">
      <div class="cf-card-header">🏰 Raid Progress</div>
      <div class="cf-card-body">
        <?php if ($progress && !empty($progress['raid_progression'])): ?>
          <?php foreach($progress['raid_progression'] as $raidKey => $prog): ?>
            <?php $nm=$prog['normal_bosses_killed']??0; $total=$prog['total_bosses']??1;
                  $hm=$prog['heroic_bosses_killed']??0; $mm=$prog['mythic_bosses_killed']??0; ?>
            <div style="margin-bottom:1.2rem;">
              <div style="font-weight:700;margin-bottom:.6rem;font-size:.9rem;">
                <?= htmlspecialchars(ucwords(str_replace('-',' ',$raidKey))) ?>
              </div>
              <?php foreach([['Normal','#aad372',$nm],['Heroic','#f48cba',$hm],['Mythic','#c41e3a',$mm]] as [$diff,$col,$kills]): ?>
              <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;font-size:.8rem;">
                <span style="width:50px;color:<?= $col ?>;"><?= $diff ?></span>
                <div style="flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                  <div style="width:<?= $total>0?round(($kills/$total)*100):0 ?>%;height:100%;background:<?= $col ?>;border-radius:3px;"></div>
                </div>
                <span style="color:var(--muted);width:35px;text-align:right;"><?= $kills ?>/<?= $total ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="cf-block-empty">Geen raid data beschikbaar.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Top leden -->
    <div class="cf-card">
      <div class="cf-card-header">👥 Guild Roster (top leden)</div>
      <div class="cf-card-body">
        <?php if ($roster && !empty($roster['members'])): ?>
          <?php $top = array_slice($roster['members'], 0, 12); ?>
          <?php foreach($top as $m):
            $char = $m['character'] ?? [];
            $name = $char['name'] ?? '?';
            $cls  = $char['playable_class']['name'] ?? '';
            $lvl  = $char['level'] ?? 0;
            $col  = $classColors[$cls] ?? '#e2e8f0';
            $icon = $m['rank'] === 0 ? '👑 ' : '';
          ?>
          <div style="display:flex;justify-content:space-between;align-items:center;
                      padding:.35rem 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.83rem;">
            <span style="font-weight:600;color:<?= htmlspecialchars($col,ENT_QUOTES) ?>"><?= $icon ?><?= htmlspecialchars($name) ?></span>
            <span style="color:var(--muted)"><?= htmlspecialchars($cls) ?></span>
            <span style="color:var(--gold);font-size:.75rem;">Lv.<?= $lvl ?></span>
          </div>
          <?php endforeach; ?>
          <a href="/wow/guild" class="cf-block-more">Volledig roster →</a>
        <?php else: ?>
          <p class="cf-block-empty">Geen roster beschikbaar.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</body>
</html>

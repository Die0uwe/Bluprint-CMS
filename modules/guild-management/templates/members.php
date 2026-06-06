<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
$wowClassColors = [
    'Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
    'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
    'Paladin'=>'#f48cba','Priest'=>'#ffffff','Rogue'=>'#fff468',
    'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a',
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Guild Roster — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
<style>
  .roster-wrap { max-width:1100px; margin:2rem auto; padding:0 1.5rem; }
  .roster-filters { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem; }
  .roster-filter-btn {
    padding:.35rem .9rem; border-radius:6px; font-size:.8rem; font-weight:600;
    border:1px solid var(--border); background:transparent; color:var(--text-dim);
    cursor:pointer; text-decoration:none; transition:all .15s;
  }
  .roster-filter-btn:hover, .roster-filter-btn.active {
    background:rgba(108,61,244,.15); border-color:var(--accent); color:var(--accent2);
  }
  .roster-grid {
    display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1rem;
  }
  .roster-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:var(--radius); padding:1rem 1.2rem;
    display:flex; align-items:center; gap:.9rem;
    transition:border-color .2s, box-shadow .2s;
  }
  .roster-card:hover { border-color:var(--accent); box-shadow:var(--glow); }
  .roster-avatar {
    width:44px; height:44px; border-radius:50%;
    background:var(--surface2); border:2px solid var(--border);
    display:flex; align-items:center; justify-content:center; font-size:1.3rem;
    flex-shrink:0;
  }
  .roster-name { font-weight:700; font-size:.95rem; }
  .roster-rank { font-size:.75rem; font-weight:700; margin-top:.1rem; }
  .roster-meta { font-size:.75rem; color:var(--muted); margin-top:.2rem; display:flex; gap:.5rem; }
  .roster-ilvl { color:var(--gold); font-weight:700; }
</style>
</head>
<body>
<div class="roster-wrap">
  <div style="margin-bottom:1.5rem;">
    <div class="cf-breadcrumb" style="font-size:.82rem;color:var(--muted);margin-bottom:.5rem;">
      <a href="/">Home</a> → <a href="/guild">Guild</a> → Roster
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;">⚔️ Guild Roster</h1>
    <p style="color:var(--text-dim);margin-top:.3rem;"><?= count($members) ?> actieve leden</p>
  </div>

  <!-- Rang filters -->
  <div class="roster-filters">
    <a href="/guild/members" class="roster-filter-btn <?= !isset($_GET['rank']) ? 'active' : '' ?>">Alle rangen</a>
    <?php foreach($ranks as $r): ?>
    <a href="/guild/members?rank=<?= $r['id'] ?>"
       class="roster-filter-btn <?= ($_GET['rank'] ?? '') == $r['id'] ? 'active' : '' ?>"
       style="<?= isset($_GET['rank']) && $_GET['rank'] == $r['id'] ? 'border-color:'.htmlspecialchars($r['color'] ?? '#6c3df4', ENT_QUOTES).';color:'.htmlspecialchars($r['color'] ?? '#6c3df4', ENT_QUOTES) : '' ?>">
      <?= htmlspecialchars($r['display_name']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Roster grid -->
  <div class="roster-grid">
    <?php foreach($members as $m):
      $classColor = $wowClassColors[$m['class'] ?? ''] ?? '#e2e8f0';
      $rankColor  = $m['rank_color'] ?? '#64748b';
      $classIcon  = ['Death Knight'=>'🩸','Demon Hunter'=>'👁️','Druid'=>'🌿','Evoker'=>'🐉',
                     'Hunter'=>'🏹','Mage'=>'🔵','Monk'=>'☯️','Paladin'=>'⚔️','Priest'=>'✨',
                     'Rogue'=>'🗡️','Shaman'=>'⚡','Warlock'=>'🟣','Warrior'=>'🛡️'][$m['class'] ?? ''] ?? '⚔️';
    ?>
    <div class="roster-card">
      <div class="roster-avatar" style="border-color:<?= htmlspecialchars($classColor, ENT_QUOTES) ?>">
        <?= $classIcon ?>
      </div>
      <div>
        <div class="roster-name" style="color:<?= htmlspecialchars($classColor, ENT_QUOTES) ?>">
          <?= htmlspecialchars($m['character_name']) ?>
        </div>
        <?php if ($m['rank_name']): ?>
        <div class="roster-rank" style="color:<?= htmlspecialchars($rankColor, ENT_QUOTES) ?>">
          <?= htmlspecialchars($m['rank_name']) ?>
        </div>
        <?php endif; ?>
        <div class="roster-meta">
          <?php if ($m['spec'] && $m['class']): ?>
            <span><?= htmlspecialchars($m['spec']) ?> <?= htmlspecialchars($m['class']) ?></span>
          <?php endif; ?>
          <?php if ($m['item_level']): ?>
            <span class="roster-ilvl">⚙️ <?= (int)$m['item_level'] ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($members)): ?>
      <p style="color:var(--muted);grid-column:1/-1;">Geen leden gevonden.</p>
    <?php endif; ?>
  </div>

  <div style="margin-top:2rem;text-align:center;">
    <a href="/guild/apply" class="cf-btn">⚔️ Aanmelden voor de guild →</a>
  </div>
</div>
</body>
</html>

<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
$wowClasses = ['Death Knight','Demon Hunter','Druid','Evoker','Hunter',
               'Mage','Monk','Paladin','Priest','Rogue','Shaman','Warlock','Warrior'];
$specs = ['Blood','Frost','Unholy','Havoc','Vengeance','Balance','Feral','Guardian',
          'Restoration','Devastation','Preservation','Augmentation','Beast Mastery',
          'Marksmanship','Survival','Arcane','Fire','Subtlety','Windwalker','Brewmaster',
          'Mistweaver','Holy','Protection','Retribution','Discipline','Shadow',
          'Assassination','Outlaw','Elemental','Enhancement','Affliction','Demonology',
          'Destruction','Arms','Fury'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Guild Aanmelding — Blueprint CMS</title>
<link rel="stylesheet" href="/assets/css/blueprint.css">
</head>
<body>
<?php if (isset($_GET['success'])): ?>
<div style="max-width:600px;margin:4rem auto;text-align:center;">
  <div style="font-size:4rem;margin-bottom:1rem;">⚔️</div>
  <h1 style="font-size:1.8rem;font-weight:800;color:var(--success);">Aanmelding Ingediend!</h1>
  <p style="color:var(--text-dim);margin-top:.5rem;">We nemen zo snel mogelijk contact met je op.</p>
  <a href="/guild" class="cf-btn" style="margin-top:1.5rem;display:inline-flex;">Terug naar Guild</a>
</div>
<?php else: ?>
<div style="max-width:680px;margin:3rem auto;padding:0 1.5rem;">
  <div style="margin-bottom:1.5rem;">
    <div class="cf-breadcrumb" style="font-size:.82rem;color:var(--muted);margin-bottom:.5rem;">
      <a href="/">Home</a> → <a href="/guild">Guild</a> → Aanmelding
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;">⚔️ Guild Aanmelding</h1>
    <p style="color:var(--text-dim);margin-top:.3rem;">Vul het formulier in en we beoordelen je aanmelding zo snel mogelijk.</p>
  </div>

  <?php if (!empty($errors)): ?>
  <div class="cf-alert cf-alert-error" style="margin-bottom:1.2rem;">
    <?php foreach($errors as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="cf-card">
    <div class="cf-card-body">
      <form method="POST" action="/guild/apply">
        <?= \CommunityFusion\Core\Security\CsrfProtection::field() ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="cf-form-group">
            <label class="cf-label">Karakternaam *</label>
            <input class="cf-input" type="text" name="character_name"
                   value="<?= htmlspecialchars($data['character_name'] ?? '', ENT_QUOTES) ?>"
                   placeholder="Jouw hoofdkarakter" required>
          </div>
          <div class="cf-form-group">
            <label class="cf-label">Item Level</label>
            <input class="cf-input" type="number" name="item_level"
                   value="<?= htmlspecialchars($data['item_level'] ?? '', ENT_QUOTES) ?>"
                   placeholder="bv. 630" min="1" max="999">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="cf-form-group">
            <label class="cf-label">Klasse *</label>
            <select class="cf-select" name="class" required>
              <option value="">— Selecteer klasse —</option>
              <?php foreach($wowClasses as $c): ?>
              <option value="<?= $c ?>" <?= ($data['class'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="cf-form-group">
            <label class="cf-label">Specialisatie *</label>
            <select class="cf-select" name="spec" required>
              <option value="">— Selecteer spec —</option>
              <?php foreach($specs as $s): ?>
              <option value="<?= $s ?>" <?= ($data['spec'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <?php if (!empty($teams)): ?>
        <div class="cf-form-group">
          <label class="cf-label">Team waarvoor je aanmeldt</label>
          <select class="cf-select" name="team_id">
            <option value="">— Geen voorkeur —</option>
            <?php foreach($teams as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= ucfirst($t['type']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <div class="cf-form-group">
          <label class="cf-label">Over jezelf * <span style="color:var(--muted);font-weight:400;">(min. 20 tekens)</span></label>
          <textarea class="cf-textarea" name="about" rows="4"
                    placeholder="Wie ben je? Waarom wil je lid worden van onze guild?"><?= htmlspecialchars($data['about'] ?? '') ?></textarea>
        </div>

        <div class="cf-form-group">
          <label class="cf-label">Raid / PvP ervaring</label>
          <textarea class="cf-textarea" name="experience" rows="3"
                    placeholder="Welke content heb je voltooid? Heroic/Mythic raids, Mythic+ scores, etc."><?= htmlspecialchars($data['experience'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
          <button type="submit" class="cf-btn">⚔️ Aanmelding Indienen →</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
</body>
</html>

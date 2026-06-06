<form method="POST">
  <p style="margin-bottom:1rem;color:var(--muted);font-size:.9rem;">
    Kies welke modules je wilt installeren. Core modules zijn altijd actief.
  </p>
  <div class="modules-grid">
    <?php
    $modules = [
      ['slug'=>'users',    'icon'=>'👤', 'name'=>'Gebruikers',     'desc'=>'Login, profielen, RBAC', 'core'=>true],
      ['slug'=>'news',     'icon'=>'📰', 'name'=>'Nieuws',         'desc'=>'Artikelen, categorieën', 'core'=>true],
      ['slug'=>'pages',    'icon'=>'📄', 'name'=>"Pagina's",       'desc'=>'CMS pagina\'s + menu', 'core'=>true],
      ['slug'=>'settings', 'icon'=>'⚙️', 'name'=>'Instellingen',   'desc'=>'Admin configuratie', 'core'=>true],
      ['slug'=>'forum',    'icon'=>'💬', 'name'=>'Forum',          'desc'=>'Discussies & topics', 'core'=>false],
      ['slug'=>'discord',  'icon'=>'🎮', 'name'=>'Discord',        'desc'=>'OAuth, widgets, sync', 'core'=>false],
      ['slug'=>'twitch',   'icon'=>'📺', 'name'=>'Twitch',         'desc'=>'Live status, embeds', 'core'=>false],
      ['slug'=>'youtube',  'icon'=>'▶️', 'name'=>'YouTube',        'desc'=>"Video's, playlists", 'core'=>false],
      ['slug'=>'guild',    'icon'=>'⚔️', 'name'=>'Guild',          'desc'=>'Leden, teams, rangen', 'core'=>false],
      ['slug'=>'minecraft','icon'=>'🟫', 'name'=>'Minecraft',      'desc'=>'Server status, spelers', 'core'=>false],
    ];
    foreach ($modules as $m):
      $checked = $m['core'] ? 'checked' : '';
      $disabled = $m['core'] ? 'disabled' : '';
    ?>
      <label class="module-card">
        <input type="checkbox" name="modules[]" value="<?= $m['slug'] ?>" <?= $checked ?> <?= $disabled ?>>
        <div class="module-icon"><?= $m['icon'] ?></div>
        <div>
          <div class="module-name"><?= $m['name'] ?></div>
          <div class="module-desc"><?= $m['desc'] ?></div>
          <?php if ($m['core']): ?><div class="module-core">Core ●</div><?php endif; ?>
        </div>
      </label>
    <?php endforeach; ?>
  </div>
  <div class="btn-row" style="margin-top:2rem;">
    <button class="btn" type="submit">🚀 Installatie Voltooien</button>
  </div>
</form>

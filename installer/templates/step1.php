<?php
// Server check weergave
$checks = [
    'PHP >= 8.3'         => PHP_VERSION_ID >= 80300,
    'PDO'                => extension_loaded('pdo'),
    'pdo_mysql'          => extension_loaded('pdo_mysql'),
    'mbstring'           => extension_loaded('mbstring'),
    'openssl'            => extension_loaded('openssl'),
    'json'               => extension_loaded('json'),
    'cURL'               => extension_loaded('curl'),
    'GD'                 => extension_loaded('gd'),
    'config/ schrijfbaar'=> is_writable(CF_ROOT . '/config') || is_dir(CF_ROOT . '/config'),
    'storage/ schrijfbaar'=> is_writable(CF_ROOT . '/storage'),
];
$allOk = !in_array(false, $checks, true);
?>
<div class="checks">
  <?php foreach ($checks as $label => $ok): ?>
    <div class="check-item">
      <span class="<?= $ok ? 'check-ok' : 'check-fail' ?>"><?= $ok ? '✅' : '❌' ?></span>
      <span><?= htmlspecialchars($label) ?></span>
      <?php if ($label === 'PHP >= 8.3'): ?>
        <span style="margin-left:auto;color:var(--muted);font-size:.8rem;">v<?= PHP_VERSION ?></span>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($allOk): ?>
  <form method="POST">
    <div class="btn-row">
      <button class="btn" type="submit">Doorgaan →</button>
    </div>
  </form>
<?php else: ?>
  <div class="alert alert-error" style="margin-top:1rem;">
    Los bovenstaande problemen op en herlaad de pagina.
  </div>
<?php endif; ?>

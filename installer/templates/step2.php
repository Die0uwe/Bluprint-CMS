<form method="POST">
  <div class="form-row-3">
    <div class="form-group">
      <label>Database Host</label>
      <input type="text" name="db_host" value="127.0.0.1" required>
    </div>
    <div class="form-group">
      <label>Poort</label>
      <input type="number" name="db_port" value="3306" required>
    </div>
  </div>
  <div class="form-group">
    <label>Database Naam</label>
    <input type="text" name="db_name" placeholder="blueprint_cms" required>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label>Gebruiker</label>
      <input type="text" name="db_user" placeholder="root" required>
    </div>
    <div class="form-group">
      <label>Wachtwoord</label>
      <input type="password" name="db_pass" placeholder="(leeg = geen wachtwoord)">
    </div>
  </div>
  <div class="btn-row">
    <button class="btn" type="submit">Verbinden & Schema Importeren →</button>
  </div>
</form>

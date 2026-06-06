# Slayer Alliance Suite — Roster & Armory v2.0
## Integratie handleiding · 2025-06-06

---

## BESTANDEN

```
modules/
  roster.php       ← Guild Roster module (shortcode + admin tab)
  armory.php       ← Character Armory module (shortcode + admin tab)
assets/
  roster.css       ← Roster CSS (dark Slayer Alliance thema)
  armory.css       ← Armory CSS (dark Slayer Alliance thema)
```

---

## VEREISTEN

- WordPress 6.x+
- PHP 8.1+  (union types + match expressions gebruikt)
- Blizzard Developer account → https://develop.battle.net
- `sa_get_valid_token()` en `sa_get_core_settings()` in core aanwezig ✅

---

## STAP 1 — Bestanden plaatsen

Kopieer naar je plugin folder:

```
/wp-content/plugins/dieouwe-master-suite/modules/roster.php
/wp-content/plugins/dieouwe-master-suite/modules/armory.php
/wp-content/plugins/dieouwe-master-suite/assets/roster.css
/wp-content/plugins/dieouwe-master-suite/assets/armory.css
```

De module loader in de core (`glob('modules/*.php')`) pikt ze automatisch op.

---

## STAP 2 — Activeren in admin

Ga naar **SA Suite → Modules** en zet `roster` en `armory` op **Yes**.

---

## STAP 3 — Shortcodes plaatsen

**Guild Roster:**
```
[guild_roster]
[guild_roster show_avatars="yes" show_alts="no" per_page="20"]
```

**Armory (zoekpagina):**
```
[sa_armory]
```

**Armory (direct karakter):**
```
[sa_armory char="Dieouwe" realm="sporeggar"]
```
of via URL: `/armory/?char=Dieouwe&realm=sporeggar`

---

## BLIZZARD API ENDPOINTS GEBRUIKT

| Module  | Methode | Endpoint                                                        | Namespace    |
|---------|---------|------------------------------------------------------------------|--------------|
| Roster  | GET     | `/data/wow/guild/{realm}/{guild}/roster`                        | `profile-eu` |
| Armory  | GET     | `/profile/wow/character/{realm}/{name}`                         | `profile-eu` |
| Armory  | GET     | `/profile/wow/character/{realm}/{name}/character-media`         | `profile-eu` |
| Armory  | GET     | `/profile/wow/character/{realm}/{name}/equipment`               | `profile-eu` |
| Armory  | GET     | `/profile/wow/character/{realm}/{name}/achievements/statistics` | `profile-eu` |

> ⚠ Token ALTIJD via `Authorization: Bearer {token}` header — NIET als query string.
> Dit is verplicht sinds augustus 2024 (Blizzard API gateway wijziging).

---

## CACHING

| Data           | Transient key                           | TTL      |
|----------------|-----------------------------------------|----------|
| Guild roster   | `sa_guild_roster_v2`                    | 30 min   |
| Char media     | `sa_char_media_{md5(realm_name)}`       | 24 uur   |
| Char armory    | `sa_armory_{md5(realm_name)}`           | 1 uur    |

Cache legen via admin tab → **"Cache legen"** knop (AJAX, nonce beveiligd).

---

## SECURITY CHECKLIST

- [x] Alle `$_GET` / `$_POST` via `sanitize_text_field()` + `sanitize_title()`
- [x] Alle output via `esc_html()` / `esc_url()` / `esc_attr()`
- [x] AJAX handlers met `check_ajax_referer()` + `current_user_can()`
- [x] Token via `Authorization` header (niet in URL)
- [x] `$wpdb->esc_like()` bij LIKE queries
- [x] Geen hardcoded credentials — alles via `sa_get_valid_token()`

---

## BEKENDE LIMITATIES

1. **Avatar laden is traag** bij eerste bezoek zonder cache — elke kaart doet een API call.
   → Oplossing: `show_avatars="no"` of een WP-Cron job die caches voorvult.

2. **Equipment icons** worden niet getoond (requires tweede API call per item).
   → Toekomstige versie: bulk icon loader via Blizzard Game Data API.

3. **Spec detectie** vereist een aparte `/character/spec` call — nu al in summary meegeleverd.

---

## ROSTER RANK LABELS AANPASSEN

In `functions.php` of een custom plugin:

```php
add_filter('sa_roster_rank_labels', function($labels) {
    $labels[0] = '👑 Slayer King';
    $labels[1] = '⚔ Warlord';
    return $labels;
});
```

---

*DieOuwe Slayer Alliance Suite · slayeralliance.com · discord.gg/y8Pu5qsEbQ*

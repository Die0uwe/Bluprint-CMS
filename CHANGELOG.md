<!--
============================================================================
Copyright (C) 2026  DieOuwe (https://www.dieouwe.nl / https://www.slayeralliance.com)

This work is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
============================================================================
-->

# Changelog — Blueprint CMS

Alle noemenswaardige wijzigingen worden in dit bestand bijgehouden.

Format gebaseerd op [Keep a Changelog](https://keepachangelog.com/nl/1.0.0/).
Versienummering volgt [Semantic Versioning](https://semver.org/lang/nl/).

---

## [1.0.0] — 2026-06-06 — Sprint 1: Core Foundation

### Toegevoegd

**Core Framework**
- `Application.php` — Bootstrap klasse met DI Container setup en module loader
- `Container.php` — PSR-11 Dependency Injection Container met auto-resolve via Reflection
- `Router.php` — HTTP router met named parameters, middleware pipeline en core routes
- `Request.php` — HTTP request wrapper (PSR-7 geïnspireerd)
- `Response.php` — HTTP response met automatische security headers

**Hook Systeem**
- `HookManager.php` — WordPress-achtig action/filter systeem met prioriteitsondersteuning

**Database Laag**
- `Connection.php` — PDO wrapper, prepared statements only, transaction helper
- `QueryBuilder.php` — Fluent query builder met method chaining
- `schema.sql` — Volledig database schema (users, roles, permissions, modules, blocks, news, pages, settings)

**Auth & Security**
- `AuthManager.php` — Login, logout, sessie, argon2id wachtwoord hashing
- `JWTManager.php` — JWT generatie + validatie (HS256)
- `RBACManager.php` — Role-based access control met cache-ondersteuning
- `CsrfProtection.php` — CSRF token generatie en validatie

**Cache Systeem**
- `CacheManager.php` — PSR-16 cache facade met `remember()` helper
- `FileCache.php` — File-based cache driver met TTL ondersteuning

**Queue Systeem**
- `Job.php` — Abstract base class voor queue jobs
- `QueueManager.php` — Database-gebaseerde job queue

**Module & Block API**
- `ModuleInterface.php` — Contract interface voor alle modules
- `BlockInterface.php` — Contract interface voor block types
- `AbstractBlock.php` — Abstract base class voor block implementaties

**Repository Laag**
- `NewsRepository.php` — Nieuws CRUD met cache-aside pattern
- `PageRepository.php` — Pagina's ophalen + menu query
- `SettingsRepository.php` — Site-instellingen met type casting

**API & Middleware**
- `AuthMiddleware.php` — Sessie + JWT authenticatie middleware

**Project Setup**
- `composer.json` — PSR-4 autoloading, dependencies definitie
- `.env.example` — Environment variabelen template
- `public/index.php` — Front controller
- `public/.htaccess` — URL rewriting + security rules
- `cli/console.php` — CLI entry point
- `README.md` — Volledige projectdocumentatie
- Volledige mapstructuur aangemaakt

### Technische details

- PHP 8.3+ vereist
- PSR-4, PSR-7, PSR-11, PSR-14, PSR-16 compliant
- Alle bestanden voorzien van GPL-3.0 copyright header + file card

---

<!--
╔══════════════════════════════════════════════════════════════════════╗
║                         FILE CARD                                    ║
╠══════════════════════════════════════════════════════════════════════╣
║  File         : CHANGELOG.md                                         ║
║  Role         : Docs                                                 ║
║  Version      : 1.0.0                                                ║
║  Created      : 2026-06-06                                           ║
║  Last Updated : 2026-06-06  03:00                                    ║
║  Status       : New                                                  ║
║  Notes        : Sprint 1 initiële changelog                          ║
╠══════════════════════════════════════════════════════════════════════╣
║  Created by Dieouwe                                                  ║
║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
║  📦 curseforge.com/members/dieouwe/projects                         ║
║  💬 discord.gg/y8Pu5qsEbQ                                           ║
╚══════════════════════════════════════════════════════════════════════╝
-->

## [1.1.0] — 2026-06-06 — Sprint 2: Installer + Admin + Frontend

### Toegevoegd

**Web Installer (5 stappen)**
- `installer/InstallerCore.php` — Installatie sessie, config schrijver, schema importer
- `installer/index.php` — Installer front controller met step routing
- `installer/steps/Step1.php` — Server requirements check (PHP, extensies, schrijfrechten)
- `installer/steps/Step2.php` — Database verbinding + schema import
- `installer/steps/Step3.php` — Site instellingen (naam, URL, taal, tijdzone)
- `installer/steps/Step4.php` — Admin account aanmaken (argon2id)
- `installer/steps/Step5.php` — Module selectie + config.php genereren
- `installer/templates/layout.php` — Gaming dark wizard UI met stap-indicator
- `installer/templates/step1-5.php` — Visuele formulieren per stap

**Frontend Controllers**
- `src/Modules/News/NewsController.php` — Nieuws index + detail pagina
- `src/Modules/Pages/PageController.php` — Homepage + statische pagina's
- `src/Modules/Users/AuthController.php` — Login, logout, registreren + CSRF
- `src/Modules/Settings/AdminController.php` — Admin dashboard routing

**Admin Dashboard**
- `src/Modules/Settings/views/dashboard.php` — Volledig admin dashboard met sidebar, stat-cards, snelle acties, activiteitsfeed, systeem status

**Template Engine**
- `src/Core/Template/ThemeManager.php` — Twig 3.x integratie, theme.json loader, custom functies (asset, url, csrf_field)

**Default Gaming Dark Thema**
- `themes/default/theme.json` — Thema manifest + kleurenpalette
- `themes/default/templates/layout.twig` — Hoofd layout met zones (header, sidebars, footer)
- `themes/default/templates/home.twig` — Homepage met nieuws grid
- `themes/default/templates/news/index.twig` — Nieuws overzicht
- `themes/default/templates/auth/login.twig` — Login formulier
- `themes/default/templates/auth/register.twig` — Registreer formulier

**Frontend Assets**
- `public/assets/css/blueprint.css` — Volledig gaming dark CSS (CSS variables, layout, cards, forms, responsive)
- `public/assets/js/blueprint.js` — Core JS (nav-highlight, flash messages, scroll animaties)

## [1.2.0] — 2026-06-06 — Sprint 3: Block API + Drag & Drop Layout

### Toegevoegd

**Block Registry (Core)**
- `src/Core/Block/BlockRegistry.php` — Centrale registry voor alle block types. Zone rendering met cache-ondersteuning, createBlock/updateBlock/deleteBlock CRUD, drag & drop positie-opslag via `updatePositions()`, DB-sync van block types

**Block Types (6 stuks)**
- `src/Blocks/Types/TextBlock.php` — Tekst blok met nl2br output, 1u cache
- `src/Blocks/Types/HtmlBlock.php` — Vrij HTML blok voor admins, 30min cache
- `src/Blocks/Types/NewsBlock.php` — Laatste X artikelen als compacte lijst, 5min cache
- `src/Blocks/Types/LoginBlock.php` — Login formulier of welkom-bericht (user-aware, geen cache)
- `src/Blocks/Types/StatsBlock.php` — Site statistieken (leden/artikelen/paginas), 10min cache
- `src/Blocks/Types/AdBlock.php` — Advertentie/banner blok met externe URL, 1u cache

**Block Controller + Admin UI**
- `src/Modules/Blocks/BlockController.php` — CRUD endpoints + drag & drop JSON API (`/api/v1/blocks/positions`, `/api/v1/blocks/zones`)
- `src/Modules/Blocks/views/index.php` — Volledige drag & drop Block Manager admin UI. CSS Grid site-preview met 6 zones (header/topmenu/sidebars/content/footer), block palette met drag-from, zone-droptargets, placed block editing/delete/toggle, Add Block modal, Ajax API-calls met toast feedback

**Router uitgebreid**
- Block admin routes: GET/POST `/admin/blocks`, `/admin/blocks/{id}/update`, `/admin/blocks/{id}/delete`
- Block API routes: GET `/api/v1/blocks/zones`, POST `/api/v1/blocks/positions`

**CSS uitgebreid**
- `public/assets/css/blueprint.css` — Block-specifieke CSS: `.cf-block-*` classes voor Text, News lijst, Login, Stats, Ad blocks

## [1.3.0] — 2026-06-06 — Sprint 4: Discord OAuth + Twitch Integratie

### Toegevoegd

**OAuth2 Architectuur (Abstract Base)**
- `src/Core/Auth/OAuth/OAuthClient.php` — Abstract OAuth2 base class. Bouwt authorization URL op, wisselt auth code in voor tokens, haalt user-data op, slaat tokens AES-256-GCM encrypted op in cf_user_oauth, biedt token refresh, HTTP helpers (POST/GET via cURL)

**Discord Module** (`modules/discord/`)
- `module.json` — Module manifest: slug, class, hooks, permissions, settings schema
- `src/DiscordOAuth.php` — Discord OAuth2 client. Authorization URL, token exchange, user fetch, guild member API, bot token support, widget data, avatar URL helper
- `src/DiscordModule.php` — Module boot: registreert blocks, sync job op login, OAuth routes via hook
- `src/DiscordOAuthController.php` — OAuth flow controller: redirect → callback → rol synchronisatie → DB opslag
- `src/DiscordWidgetBlock.php` — Officiële Discord widget iframe embed (configureerbaar thema/grootte)
- `src/DiscordOnlineBlock.php` — Online leden via Guild Widget API (cached 60s), join-knop, avatar + status

**Twitch Module** (`modules/twitch/`)
- `module.json` — Module manifest
- `src/TwitchOAuth.php` — Twitch OAuth2 (Authorization Code + Client Credentials). Helix API, live status, channel info, follower count, app token ophalen
- `src/TwitchModule.php` — Module boot: blocks registreren, OAuth routes
- `src/TwitchOAuthController.php` — OAuth flow: redirect → callback → koppeling opslaan
- `src/TwitchLiveBlock.php` — Live/offline status block. Thumbnail, viewer count, game naam (cached 90s)
- `src/TwitchStreamBlock.php` — Twitch player embed (optioneel met chat, muted, hoogte)

**Discord Rol Synchronisatie**
- DB schema: `cf_discord_role_mapping` + `cf_discord_sync_log`
- Queue-based sync bij elke login: Discord rollen → CMS rollen (auto-assign + auto-remove)
- RBAC cache invalidatie na sync

**CLI Queue Worker**
- `cli/commands/QueueWorkerCommand.php` — Database queue worker: reserveer, verwerk, retry, fail-markering
- `cli/commands/CacheClearCommand.php` — Cache wissen (file + Twig cache)

**CSS uitgebreid**
- Discord: widget, online leden, avatar, status dot
- Twitch: live badge, thumbnail, channel link, embed wrap

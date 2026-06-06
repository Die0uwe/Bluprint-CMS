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

## [1.4.0] — 2026-06-06 — Sprint 5: Guild Management + WoW + Minecraft + FiveM

### Toegevoegd

**Guild Management Module** (`modules/guild-management/`)
- `GuildModule.php` — Boot + installer: DB schema (cf_guild_ranks, cf_guild_members, cf_guild_teams, cf_guild_applications), seed-rangen (GM/Officer/Raider/Trial/Social)
- `GuildController.php` — Publieke pagina's: index, roster (met rang-filter), aanmelding
- `GuildAdminController.php` — Admin: aanmeldingen beheren, approve (→ trial lid), reject
- `GuildMembersBlock.php` — Blok: actieve leden gesorteerd op rang, class-iconen, iLvl
- `GuildInfoBlock.php` — Blok: statistieken (leden/aanmeldingen), teams, aanmeld-knop
- `GuildRecruitmentBlock.php` — Blok: vrije tekst + actief wervende teams
- `templates/apply.php` — Aanmeldingsformulier: karakter/klasse/spec/iLvl/team/motivatie
- `templates/members.php` — Roster met rang-filters, kleur-coded karakterkaarten

**World of Warcraft Module** (`modules/warcraft/`)
- `BlizzardApiClient.php` — Battle.net OAuth2 Client Credentials API client:
  guild roster, guild info, guild activity, character profiel, equipment,
  Mythic+ score via Raider.IO, raid progress via Raider.IO, realm status.
  Volledige response cache (PSR-16). Regio/locale configureerbaar.
- `WarcraftModule.php` — Boot + blok registratie + admin/public routes
- `WarcraftController.php` — Publiek: index (guild + progress), guild roster, character
- `WarcraftAdminController.php` — Admin instellingen: API keys, realm, guild, regio
- `WowGuildRosterBlock.php` — Blok: live roster via Blizzard API, klasse-kleuren, rang-iconen
- `WowMythicProgressBlock.php` — Blok: Normal/Heroic/Mythic progress bars via Raider.IO
- `WowCharacterBlock.php` — Blok: character profiel + iLvl + Raider.IO M+ score
- `templates/index.php` — WoW pagina: guild hero banner, raid progress, top roster
- `templates/admin.php` — Admin settings form met API-instructies

**Minecraft Module** (`modules/minecraft/`)
- `MinecraftModule.php` — Module boot + routes
- `MinecraftStatusBlock.php` — Server status via mcsrvstat.us API: online/offline, versie, spelers-count, MOTD, spelerslijst (60s cache)

**FiveM Module** (`modules/fivem/`)
- `FiveMModule.php` — Module boot + routes
- `FiveMStatusBlock.php` — Server status via FXServer /info.json + /players.json: online/offline, spelerlijst met ping, bezetting-balk (45s cache)

**CSS uitgebreid**
- Guild: member-rows, stats, teams, recruitment
- WoW: guild naam gradient, progress bars, character stats, Raider.IO score kleuren
- Minecraft: server status, MOTD, spelers-chips
- FiveM: status, spelers-chips, ping-badge

## [1.5.0] — 2026-06-06 — Sprint 6: REST API v1 Compleet + Ollama AI Integratie

### Toegevoegd

**REST API v1 — Volledig OAS-compliant**
- `src/Api/V1/StatusController.php` — GET /api/v1/status: versie, PHP, DB status, timestamp
- `src/Api/V1/AuthController.php` — POST /api/v1/auth/login (JWT token), GET /api/v1/auth/me
- `src/Api/V1/UsersController.php` — GET /api/v1/users (paginering, admin only), GET /api/v1/users/{id}
- `src/Api/V1/ContentController.php` — GET /api/v1/news, /api/v1/news/{slug}, /api/v1/pages, /api/v1/blocks/zones

**Middleware**
- `src/Api/Middleware/RateLimitMiddleware.php` — 60 req/min per IP, X-RateLimit headers, 429 response
- `src/Api/Middleware/CorsMiddleware.php` — CORS headers voor alle API routes, OPTIONS preflight

**Router v1.2.0**
- Alle REST API v1 routes met CORS + RateLimit middleware
- GET /api/v1/auth/me met Auth middleware
- Middleware stacking: CORS + Auth + RateLimit combineerbaar

**Ollama AI Module** (`modules/ollama/`)
- `module.json` — Manifest: blocks, settings (host, model, timeout, system_prompt, Open WebUI)
- `OllamaClient.php` — Volledig PHP client voor Ollama REST API:
  - `generate()` — enkelvoudige prompt
  - `chat()` — multi-turn conversatie met geschiedenis
  - `embed()` — embedding vectors
  - `listModels()` — beschikbare modellen
  - `isAvailable()` — health check (3s timeout)
  - `chatViaOpenWebUI()` — OpenAI-compatible API via Open WebUI v0.9.2
  - `summarizeNews()` — nieuws samenvatting met cache
  - `analyzeGuildApplication()` — WoW guild aanmelding AI-beoordeling
  - `communityChat()` — context-aware community chatbot
  - `generateRecruitmentPost()` — AI guild recruitment tekst
- `OllamaModule.php` — Boot: blocks, routes, guild application hook (auto AI analyse)
- `OllamaChatBlock.php` — Live chat widget met AJAX, gesprekgeschiedenis, typing indicator
- `OllamaAssistantBlock.php` — Statische AI content: tips, recruitment, welkomst (1u cache)
- `OllamaApiController.php` — POST /api/ollama/chat, POST /api/ollama/summarize, GET /api/ollama/models
- `OllamaAdminController.php` — Instellingen admin, model overzicht, verbindingstest
- `templates/admin.php` — Admin UI: status badge, model lijst, settings form, Docker instructies

**Open WebUI Analyse** (zie rapport hieronder)
- Geïntegreerd als optionele backend naast directe Ollama
- OllamaClient detecteert automatisch fallback naar directe Ollama als Open WebUI niet beschikbaar

### Gewijzigd
- `src/Core/Router.php` v1.2.0 — REST API + middleware stack uitgebreid

## [1.6.0] — 2026-06-06 — Sprint 7: Marketplace

### Toegevoegd

**Marketplace Database Schema**
- `cf_marketplace_packages` — Package catalogus (slug, type, versie, download URL, tags, downloads, rating, featured, verified, premium)
- `cf_marketplace_installed` — Installatie registry (versie, pad, enabled status, update beschikbaar)
- `cf_marketplace_reviews` — Beoordelingen per package (rating, review tekst)
- Seed-data: 7 modules + 1 thema met realistisch downloadaantal

**PackageManager** (`src/Core/Marketplace/PackageManager.php`)
- `install(slug, url)` — Download ZIP → path-traversal validatie → extractie → manifest validatie → deployen → DB registreren → module installer uitvoeren
- `installFromUpload(tmp, name)` — Installatie vanuit geüpload bestand
- `uninstall(slug)` — Veiligheidscheck (core modules blokkeren) → uninstall hook → bestanden verwijderen → DB cleanup
- `update(slug)` — Bestaande installatie vervangen door nieuwste versie
- `enable(slug)` / `disable(slug)` — Module in/uitschakelen (core blokkeert)
- `checkForUpdates()` — Vergelijk geïnstalleerde vs catalogus versies (gecached 1u)
- `getCatalog(type, search, sortBy, limit, offset)` — Gefilterde/gesorteerde catalogus (gecached 5min)
- `getInstalledPackages()` — Alle geïnstalleerde packages met catalogusinfo

**InstallResult + PackageException** — Type-safe value objects

**MarketplaceController** (`src/Modules/Marketplace/MarketplaceController.php`)
- GET /admin/marketplace — Volledig admin UI (4 tabs)
- POST /admin/marketplace/install — Package installeren via URL
- POST /admin/marketplace/upload — ZIP upload installatie
- POST /admin/marketplace/uninstall — Package verwijderen
- POST /admin/marketplace/toggle — Enable/disable
- POST /admin/marketplace/update — Update naar nieuwste versie
- GET /api/v1/marketplace — Publieke catalogus API
- GET /api/v1/marketplace/installed — Geïnstalleerde packages (auth)
- GET /api/v1/marketplace/updates — Beschikbare updates (auth)

**Marketplace Admin UI** (`src/Modules/Marketplace/views/index.php`)
- Tab 1 — Browsen: package grid met zoeken, type-filters, sortering
- Tab 2 — Geïnstalleerd: toggle switches, update badges, verwijder knoppen
- Tab 3 — Updates: update-beschikbaar lijst, bulk update knop
- Tab 4 — ZIP Upload: drag & drop zone, progress bar, validatie-instructies
- Volledig Ajax (geen page reload) met toast notificaties

**Router v1.3.0** — 10 marketplace routes toegevoegd (web + API)

## [1.6.1] — 2026-06-06 — Documentatie + Analyse + Mockup

### Toegevoegd
- `docs/ANALYSE.md` — Volledig statisch analyse rapport: code metrics, security audit, PSR compliance, routes inventaris, sprint overzicht
- `docs/assets/architecture.md` — Architectuur documentatie: request flow, module structuur, block pipeline
- Frontend mockup visualisatie toegevoegd aan project documentatie

## [1.6.2] — 2026-06-06 — Frontpage Mockup + Docs compleet

### Toegevoegd
- `docs/MOCKUP-FRONTPAGE.html` — Volledig standalone HTML mockup van de Blueprint CMS frontpage. Gaming dark thema, 3-kolom layout (sidebar + content + sidebar), hero sectie, nieuws grid, events, Discord widget, Twitch live, Ollama AI chat block, WoW raid progress, community stats, responsive footer. Voorbeeld guild: Slayer Alliance — EU Sporeggar.

### Zichtbare features in de mockup
- Header met logo, navigatie, Discord + login knoppen
- Hero met guild naam, progression stats (8/8H, 4/8M), CTA knoppen
- Linker sidebar: login form (inclusief Discord OAuth), WoW raid progress bars (Blizzard + Raider.IO), community statistieken
- Main content: featured nieuws artikel + 2-kolom nieuws grid, events met datum-badge + type-tag
- Rechter sidebar: Twitch live stream status, Discord online leden met status-indicator, Ollama AI community chatbot
- Footer met guild naam, links, "Powered by Blueprint CMS v1.6.0"

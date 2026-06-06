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

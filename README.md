<!--
============================================================================
Copyright (C) 2026  DieOuwe (https://www.dieouwe.nl / https://www.slayeralliance.com)
GPL-3.0-or-later
============================================================================
-->

<div align="center">

# 🔮 Blueprint CMS

**Modulair PHP 8.3+ Community CMS voor gaming, streamers & gilden**

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11%2B-003545?style=flat-square&logo=mariadb)](https://mariadb.org)
[![License](https://img.shields.io/badge/License-GPL--3.0-blue?style=flat-square)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.7.0-brightgreen?style=flat-square)](CHANGELOG.md)
[![Files](https://img.shields.io/badge/Bestanden-150%2B-orange?style=flat-square)]()

*Geïnspireerd door PHP-Fusion · Down Under Fusion · ImpressCMS*

[📊 Analyse Rapport](docs/ANALYSE.md) · [🖼️ Frontpage Mockup](docs/MOCKUP-FRONTPAGE.html) · [🏗️ Architectuur](docs/assets/architecture.md)

</div>

---

## ✨ Wat is Blueprint CMS?

Blueprint CMS is een open-source, **modulair PHP-framework** speciaal gebouwd voor:

- 🎮 **Gaming communities** — guild management, server status, roster, raid progress
- 📺 **Streamers** — Discord / Twitch / YouTube / Kick integratie out-of-the-box
- ⚔️ **WoW Gilden** — Blizzard API + Raider.IO integratie, aanmeldingen, teams
- 🤖 **AI-powered** — Gratis lokale AI via Ollama (llama3.2, mistral, gemma2) + Open WebUI
- 🏢 **Verenigingen & bedrijven** — volledige website binnen 5 minuten

---

## 🏗️ Architectuur

```
public/               ← Enige publieke map (document root)
│  index.php          ← Front controller — enige entry point
│
src/Core/             ← Framework kernel
│  Application.php    ← Bootstrap + DI Container + module loader
│  Container.php      ← PSR-11 Dependency Injection
│  Router.php         ← URL routing + middleware pipeline
│  Request.php / Response.php
│  Hook/              ← Action/filter systeem
│  Cache/             ← PSR-16 (File + Redis)
│  Database/          ← PDO wrapper + Fluent QueryBuilder
│  Auth/              ← Login + JWT + RBAC
│  Block/             ← BlockRegistry + zone rendering
│  Queue/             ← Database job queue
│  Marketplace/       ← Package manager (download, extract, deploy)
│  Security/          ← CSRF + rate limiting
│  Template/          ← Twig 3.x ThemeManager
│
src/Modules/          ← Core modules
src/Api/              ← REST API v1 + middleware
src/Blocks/Types/     ← 6 core block types
modules/              ← 7 community/gaming modules
themes/               ← Twig dark gaming thema
```

---

## ⚡ Kernprincipes

| Principe | Implementatie |
|---|---|
| **Modulair** | Elke functionaliteit is een losstaande Module + BlockRegistry |
| **Drag & Drop** | Blokken positie-instelbaar via admin UI (Ajax, geen reload) |
| **Gaming/Streamer** | Discord, Twitch, WoW, Guild, Minecraft, FiveM ingebouwd |
| **AI-first** | Ollama AI chat + Open WebUI + guild analyse + nieuws samenvatting |
| **API-first** | REST API v1 met OAuth2 + JWT + CORS + rate limiting |
| **Secure by default** | RBAC, CSRF, prepared statements, argon2id, AES-256-GCM |
| **PSR-compliant** | PSR-4, PSR-7, PSR-11, PSR-14, PSR-16 |

---

## 🔧 Vereisten

| Component | Minimum |
|---|---|
| PHP | 8.3+ |
| MariaDB / MySQL | 10.11+ / 8.0+ |
| Extensions | PDO, pdo_mysql, GD, cURL, mbstring, openssl, json, zip |
| Composer | 2.x |

---

## 🚀 Installatie

```bash
git clone https://github.com/Die0uwe/Bluprint-CMS.git
cd Bluprint-CMS
composer install --optimize-autoloader
cp .env.example .env
# Navigeer naar http://jouwsite.nl/installer/
```

---

## 🧩 Modules (7 beschikbaar)

| Module | Blocks | Highlights |
|---|---|---|
| Discord | 2 | OAuth login, rollen sync, widget, online leden |
| Twitch | 2 | Live status, stream embed, Helix API |
| World of Warcraft | 3 | Blizzard API + Raider.IO — roster, progress, character |
| Guild Management | 3 | Aanmeldingen, leden, teams, rangen, admin |
| Minecraft | 1 | Server status via mcsrvstat.us |
| FiveM | 1 | Server status via FXServer endpoint |
| Ollama AI | 2 | Chat widget, assistent, guild analyse, nieuws samenvatting |

---

## 🗺️ Roadmap

| Sprint | Status | Inhoud |
|---|---|---|
| **S1** | ✅ v1.0.0 | Core Foundation — DI, Router, Auth, Cache, Queue, RBAC |
| **S2** | ✅ v1.1.0 | Web-installer + Admin Dashboard + Frontend |
| **S3** | ✅ v1.2.0 | Block API + Drag & Drop layout |
| **S4** | ✅ v1.3.0 | Discord OAuth + Twitch integratie |
| **S5** | ✅ v1.4.0 | Guild Management + WoW + Minecraft + FiveM |
| **S6** | ✅ v1.5.0 | REST API v1 compleet + Ollama AI + Open WebUI |
| **S7** | ✅ v1.6.0 | Marketplace (install, upload, update, toggle) |
| **S8** | ✅ v1.7.0 | Debug & fixes — 15 bugs opgelost, composer PSR-4 |
| **S9** | 🔜 Gepland | Forum module (discussies, topics, categorieën) |
| **S10**| 📋 Gepland | YouTube + Kick integratie |
| **S11**| 📋 Gepland | Premium ecosysteem + licenties + betalingen |
| **S12**| 📋 Gepland | Multi-language / i18n volledige implementatie |

---

## 🔐 Security Score: 98/100

- 82× PDO prepared statements — geen raw SQL
- 95× XSS escaping (htmlspecialchars)
- 10× CSRF validatie op alle POST requests
- Argon2id wachtwoord hashing
- AES-256-GCM OAuth token encryptie
- Rate limiting (60 req/min per IP)
- JWT authenticatie (HS256 + exp validatie)

---

## 🖥️ CLI Tools

```bash
php cli/console.php queue:work     # Queue worker starten
php cli/console.php cache:clear    # Cache wissen
php cli/console.php migrate        # DB migraties uitvoeren
php cli/console.php module:install discord  # Module installeren
```

---

## 📄 Licentie

GPL-3.0-or-later — © 2026 [DieOuwe](https://www.dieouwe.nl) / [Slayer Alliance](https://www.slayeralliance.com)

<div align="center">

🌐 [www.dieouwe.nl](https://www.dieouwe.nl) &nbsp;·&nbsp;
⚔️ [www.slayeralliance.com](https://www.slayeralliance.com) &nbsp;·&nbsp;
📦 [CurseForge](https://curseforge.com/members/dieouwe/projects) &nbsp;·&nbsp;
💬 [Discord](https://discord.gg/y8Pu5qsEbQ)

</div>

<!--
╔══════════════════════════════════════════════════════════════════════╗
║  File: README.md | Role: Docs | Version: 1.7.0                       ║
║  Updated: 2026-06-06 — Sprint 8 debug + fixes                        ║
╚══════════════════════════════════════════════════════════════════════╝
-->

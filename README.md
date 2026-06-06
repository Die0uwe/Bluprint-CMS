<!--
============================================================================
Copyright (C) 2026  DieOuwe (https://www.dieouwe.nl / https://www.slayeralliance.com)

This work is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This work is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
============================================================================
-->

<div align="center">

# 🔮 Blueprint CMS

**Modulair PHP 8.3+ Community CMS voor gaming, streamers & gilden**

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.11%2B-003545?style=flat-square&logo=mariadb)](https://mariadb.org)
[![License](https://img.shields.io/badge/License-GPL--3.0-blue?style=flat-square)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-brightgreen?style=flat-square)](CHANGELOG.md)
[![Status](https://img.shields.io/badge/Status-Sprint%201%20Complete-success?style=flat-square)]()

*Geïnspireerd door PHP-Fusion · Down Under Fusion · ImpressCMS*

</div>

---

## ✨ Wat is Blueprint CMS?

Blueprint CMS is een open-source, **modulair PHP-framework** speciaal gebouwd voor:

- 🎮 **Gaming communities** — guild management, server status, roster
- 📺 **Streamers** — Discord/Twitch/YouTube integratie out-of-the-box
- ⚔️ **Gilden** — aanmeldingen, teams, rangen, events
- 🏢 **Verenigingen & bedrijven** — volledige website binnen 5 minuten

---

## 🏗️ Architectuur — Core Foundation

```
public/               ← Enige publieke map (document root)
│  index.php          ← Front controller — enige entry point
│
src/Core/             ← Framework kernel
│  Application.php    ← Bootstrap + DI Container setup
│  Container.php      ← PSR-11 Dependency Injection
│  Router.php         ← URL routing + middleware pipeline
│  Request.php        ← HTTP request wrapper
│  Response.php       ← HTTP response + security headers
│  Hook/              ← WordPress-achtig action/filter systeem
│  Cache/             ← PSR-16 cache (File + Redis)
│  Database/          ← PDO wrapper + Fluent QueryBuilder
│  Auth/              ← Login · JWT · RBAC rechten
│  Queue/             ← Database-gebaseerde job queue
│  Security/          ← CSRF bescherming
│
src/Modules/          ← Core modules (Users, News, Pages, Forum)
src/Api/              ← REST API v1 controllers + middleware
src/Blocks/           ← Block type interfaces + abstract base
modules/              ← Installeerbare externe modules
themes/               ← Twig-gebaseerde thema's
installer/            ← Web-installer (5 stappen)
storage/              ← Cache · logs · queue · uploads
cli/                  ← Command-line tools
```

---

## ⚡ Kernprincipes

| Principe | Implementatie |
|---|---|
| **Modulair** | Elke functionaliteit is een losstaande Module of Block |
| **Uitbreidbaar** | Open Plugin API via gedefinieerde Interfaces en Hooks |
| **Drag & Drop** | Blokken zijn positie-instelbaar via admin-interface |
| **Gaming/Streamer** | Discord, Twitch, YouTube, Guild modules ingebouwd |
| **API-first** | REST API met OAuth2 + JWT voor alle core-operaties |
| **Secure by default** | RBAC, CSRF, prepared statements, argon2id, output escaping |
| **PSR-compliant** | PSR-4, PSR-7, PSR-11, PSR-14, PSR-16 |

---

## 🔧 Vereisten

| Component | Minimum |
|---|---|
| PHP | 8.3+ |
| MariaDB / MySQL | 10.11+ / 8.0+ |
| Extensions | PDO, pdo_mysql, GD, cURL, mbstring, openssl, json |
| Composer | 2.x |

---

## 🚀 Installatie

```bash
# 1. Clone de repository
git clone https://github.com/Die0uwe/Bluprint-CMS.git
cd Bluprint-CMS

# 2. Installeer PHP dependencies
composer install --optimize-autoloader

# 3. Kopieer environment template
cp .env.example .env

# 4. Open de web-installer in je browser
#    http://jouwsite.nl/installer/
```

De **5-stappen web-installer** regelt alles automatisch:

1. ✅ Servercontrole (PHP, PDO, schrijfrechten)
2. 🗄️ Database configuratie
3. 🌐 Site-instellingen
4. 👤 Admin account aanmaken
5. 🧩 Modules selecteren → klaar!

---

## 🧩 Module Overzicht

### Core Modules (altijd aanwezig)
| Module | Omschrijving |
|---|---|
| `Users` | Gebruikersbeheer + RBAC + OAuth |
| `News` | Nieuws systeem met categorieën |
| `Pages` | CMS pagina's + menu-integratie |
| `Settings` | Site-instellingen beheer |

### Community Modules (installeerbaar)
| Module | Features |
|---|---|
| `Discord` | OAuth login · Rollen sync · Widgets · Server stats |
| `Twitch` | Live status · Stream embeds · Kanaal statistieken |
| `YouTube` | Laatste video's · Livestreams · Playlists |
| `Kick` | Stream info · Live meldingen |

### Gaming Modules (installeerbaar)
| Module | Features |
|---|---|
| `Guild Management` | Leden · Teams · Rangen · Aanmeldingen · Events |
| `Minecraft` | Server status · Online spelers · Whitelist |
| `FiveM` | Server status · Speler overzicht · Discord koppeling |
| `Rust` | Server monitor · Wipe kalender |

---

## 🗺️ Roadmap

| Sprint | Status | Inhoud |
|---|---|---|
| **Sprint 1** | ✅ Klaar | Core Foundation — DI, Router, Auth, Cache, Queue, RBAC |
| **Sprint 2** | 🔄 Gepland | Web-installer, Admin dashboard, Users CRUD |
| **Sprint 3** | 📋 Gepland | Twig thema engine, News/Pages controllers |
| **Sprint 4** | 📋 Gepland | Block API, drag & drop layout |
| **Sprint 5** | 📋 Gepland | Discord OAuth, Twitch module, Queue worker |
| **Sprint 6** | 📋 Gepland | Guild management, Minecraft/FiveM status |
| **Sprint 7** | 📋 Gepland | REST API v1 compleet, Marketplace basis |

---

## 🔐 Security

- **Wachtwoorden**: `password_hash()` met `PASSWORD_ARGON2ID`
- **SQL**: Uitsluitend PDO prepared statements
- **XSS**: `htmlspecialchars()` op alle output + CSP headers
- **CSRF**: Token-gebaseerde bescherming op elke POST
- **Sessions**: `session_regenerate_id()` na login + HttpOnly + Secure
- **OAuth**: State-token validatie + AES-256-GCM encryptie tokens
- **Headers**: X-Frame-Options, X-Content-Type-Options, HSTS

---

## 🖥️ CLI Tools

```bash
# Queue worker starten
php cli/console.php queue:work --queue=default --sleep=3

# Cache wissen
php cli/console.php cache:clear

# Database migraties uitvoeren
php cli/console.php migrate

# Module installeren
php cli/console.php module:install discord
```

---

## 📄 Licentie

GPL-3.0-or-later — © 2026 [DieOuwe](https://www.dieouwe.nl) / [Slayer Alliance](https://www.slayeralliance.com)

---

<div align="center">

🌐 [www.dieouwe.nl](https://www.dieouwe.nl) &nbsp;·&nbsp;
⚔️ [www.slayeralliance.com](https://www.slayeralliance.com) &nbsp;·&nbsp;
📦 [CurseForge](https://curseforge.com/members/dieouwe/projects) &nbsp;·&nbsp;
💬 [Discord](https://discord.gg/y8Pu5qsEbQ)

</div>

<!--
╔══════════════════════════════════════════════════════════════════════╗
║                         FILE CARD                                    ║
╠══════════════════════════════════════════════════════════════════════╣
║  File         : README.md                                            ║
║  Role         : Docs                                                 ║
║  Version      : 1.0.0                                                ║
║  Created      : 2026-06-06                                           ║
║  Last Updated : 2026-06-06  03:00                                    ║
║  Status       : New                                                  ║
║  Notes        : Initiële repository setup — Sprint 1 Core            ║
╠══════════════════════════════════════════════════════════════════════╣
║  Created by Dieouwe                                                  ║
║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
║  📦 curseforge.com/members/dieouwe/projects                         ║
║  💬 discord.gg/y8Pu5qsEbQ                                           ║
╚══════════════════════════════════════════════════════════════════════╝
-->

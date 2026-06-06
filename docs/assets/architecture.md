# Blueprint CMS — Architectuur

## Request Flow

```
Browser / API Client
        │
        ▼
   public/index.php          ← Enige publieke entry point
        │
        ▼
   Application::run()        ← Bootstrap + DI Container
        │
        ├─ .env laden
        ├─ Services registreren (DB, Cache, Auth, Hooks)
        ├─ Modules laden uit cf_modules
        └─ Router::dispatch()
                │
                ├─ Middleware Pipeline
                │   ├─ CorsMiddleware       ← CORS headers
                │   ├─ AuthMiddleware       ← Sessie / JWT check
                │   └─ RateLimitMiddleware  ← 60 req/min per IP
                │
                └─ Controller
                        │
                        ├─ Repository / Service
                        │       └─ Connection (PDO prepared)
                        │               └─ MariaDB
                        │
                        ├─ CacheManager (PSR-16 FileCache)
                        ├─ BlockRegistry::renderZone()
                        ├─ ThemeManager::render() (Twig 3.x)
                        └─ Response::send()
```

## Module Architectuur

```
modules/{slug}/
├── module.json          ← Manifest (slug, name, version, class, blocks)
└── src/
    ├── {Name}Module.php ← boot() + install() + getBlocks()
    ├── {Name}Block.php  ← render() + getConfigSchema() + getCacheTtl()
    └── {Name}Controller.php
```

## Block Rendering Pipeline

```
BlockRegistry::renderZone('sidebar_right')
        │
        ├─ fetchAll(cf_blocks WHERE zone = ?)
        │
        └─ per block:
                ├─ Cache check (TTL uit DB of block type)
                ├─ BlockInterface::validateConfig()
                ├─ BlockInterface::render()
                │       └─ HTML string
                └─ Wrap in <div class="cf-block cf-block--{slug}">
```

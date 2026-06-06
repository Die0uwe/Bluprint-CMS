<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe (https://www.dieouwe.nl / https://www.slayeralliance.com)
//
// This work is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This work is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Core;

use CommunityFusion\Core\Hook\HookManager;

/**
 * HTTP Router
 * Koppelt URL-patronen aan controller-methoden.
 * Ondersteunt: GET, POST, PUT, PATCH, DELETE, middleware-groepen.
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable|string, middleware: string[]}> */
    private array $routes = [];

    /** @var string[] */
    private array $middlewareGroup = [];

    public function __construct(
        private readonly Container   $container,
        private readonly HookManager $hooks,
    ) {
        $this->registerCoreRoutes();
    }

    // ─── REGISTRATIE ─────────────────────────────────────────────────────────

    public function get(string $pattern, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $pattern, $handler, $middleware);
    }

    public function patch(string $pattern, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable|string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $pattern, $handler, $middleware);
    }

    private function addRoute(string $method, string $pattern, callable|string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->compilePattern($pattern),
            'raw'        => $pattern,
            'handler'    => $handler,
            'middleware' => [...$this->middlewareGroup, ...$middleware],
        ];
    }

    // ─── DISPATCH ────────────────────────────────────────────────────────────

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();

        // Laat modules extra routes registreren
        $this->hooks->doAction('router.routes', $this);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extraheer named route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Verwerk middleware
                $handler  = $this->resolveHandler($route['handler']);
                $pipeline = $this->buildPipeline($route['middleware'], $handler);

                return $pipeline($request);
            }
        }

        return new Response('404 — Pagina niet gevonden.', 404);
    }

    /**
     * Converteer route-patroon naar regex.
     * /users/{id} → /users/(?P<id>[^/]+)
     * /news/{slug:[a-z0-9-]+} → /news/(?P<slug>[a-z0-9-]+)
     */
    private function compilePattern(string $pattern): string
    {
        $regex = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?\}/',
            fn($m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $pattern
        );
        return '#^' . $regex . '$#';
    }

    private function resolveHandler(callable|string $handler): callable
    {
        if (is_callable($handler)) return $handler;

        // "ControllerClass@method" notatie
        [$class, $method] = explode('@', $handler, 2);
        $instance = $this->container->make($class);
        return [$instance, $method];
    }

    private function buildPipeline(array $middlewareClasses, callable $handler): callable
    {
        // Bouw middleware pipeline van buiten naar binnen
        $pipeline = $handler;

        foreach (array_reverse($middlewareClasses) as $class) {
            $middleware = $this->container->make($class);
            $next       = $pipeline;
            $pipeline   = fn(Request $req) => $middleware->handle($req, $next);
        }

        return $pipeline;
    }

    // ─── CORE ROUTES ─────────────────────────────────────────────────────────

    private function registerCoreRoutes(): void
    {
        // Homepage
        $this->get('/', 'CommunityFusion\Modules\Pages\PageController@home');

        // Authenticatie
        $this->get('/login',  'CommunityFusion\Modules\Users\AuthController@loginForm');
        $this->post('/login', 'CommunityFusion\Modules\Users\AuthController@login');
        $this->get('/logout', 'CommunityFusion\Modules\Users\AuthController@logout');
        $this->get('/register',  'CommunityFusion\Modules\Users\AuthController@registerForm');
        $this->post('/register', 'CommunityFusion\Modules\Users\AuthController@register');

        // Nieuws
        $this->get('/news',                      'CommunityFusion\Modules\News\NewsController@index');
        $this->get('/news/{slug:[a-z0-9-]+}',    'CommunityFusion\Modules\News\NewsController@show');

        // Pagina's
        $this->get('/page/{slug:[a-z0-9-/]+}',   'CommunityFusion\Modules\Pages\PageController@show');

        // Admin
        $this->get('/admin',            'CommunityFusion\Modules\Settings\AdminController@dashboard',
            ['CommunityFusion\Api\Middleware\AuthMiddleware']);
        $this->get('/admin/{path:.*}',  'CommunityFusion\Modules\Settings\AdminController@handle',
            ['CommunityFusion\Api\Middleware\AuthMiddleware']);

        // REST API v1
        $this->get('/api/v1/status',    'CommunityFusion\Api\V1\StatusController@index');
        $this->post('/api/v1/auth/login','CommunityFusion\Api\V1\AuthController@login');
        $this->get('/api/v1/users',     'CommunityFusion\Api\V1\UsersController@index',
            ['CommunityFusion\Api\Middleware\AuthMiddleware']);

        // OAuth callbacks
        $this->get('/auth/discord/callback', 'CommunityFusion\Modules\Users\OAuthController@discordCallback');
        $this->get('/auth/twitch/callback',  'CommunityFusion\Modules\Users\OAuthController@twitchCallback');
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : Router.php                                           ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : URL routing met middleware pipeline                  ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe (https://www.dieouwe.nl / https://www.slayeralliance.com)
// GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Core;

use CommunityFusion\Core\Hook\HookManager;

/**
 * HTTP Router
 * Koppelt URL-patronen aan controller-methoden.
 */
final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly Container   $container,
        private readonly HookManager $hooks,
    ) {
        $this->registerCoreRoutes();
    }

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
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path   = $request->getPath();

        $this->hooks->doAction('router.routes', $this);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                $handler  = $this->resolveHandler($route['handler']);
                $pipeline = $this->buildPipeline($route['middleware'], $handler);

                return $pipeline($request);
            }
        }

        return new Response('404 — Pagina niet gevonden.', 404);
    }

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
        [$class, $method] = explode('@', $handler, 2);
        $instance = $this->container->make($class);
        return [$instance, $method];
    }

    private function buildPipeline(array $middlewareClasses, callable $handler): callable
    {
        $pipeline = $handler;
        foreach (array_reverse($middlewareClasses) as $class) {
            $middleware = $this->container->make($class);
            $next       = $pipeline;
            $pipeline   = fn(Request $req) => $middleware->handle($req, $next);
        }
        return $pipeline;
    }

    private function registerCoreRoutes(): void
    {
        $auth = ['CommunityFusion\Api\Middleware\AuthMiddleware'];

        // ── Publiek ────────────────────────────────────────────────────────
        $this->get('/',                          'CommunityFusion\Modules\Pages\PageController@home');
        $this->get('/news',                      'CommunityFusion\Modules\News\NewsController@index');
        $this->get('/news/{slug:[a-z0-9-]+}',    'CommunityFusion\Modules\News\NewsController@show');
        $this->get('/page/{slug:[a-z0-9-/]+}',   'CommunityFusion\Modules\Pages\PageController@show');

        // ── Auth ───────────────────────────────────────────────────────────
        $this->get('/login',     'CommunityFusion\Modules\Users\AuthController@loginForm');
        $this->post('/login',    'CommunityFusion\Modules\Users\AuthController@login');
        $this->get('/logout',    'CommunityFusion\Modules\Users\AuthController@logout');
        $this->get('/register',  'CommunityFusion\Modules\Users\AuthController@registerForm');
        $this->post('/register', 'CommunityFusion\Modules\Users\AuthController@register');

        // ── Admin Dashboard ────────────────────────────────────────────────
        $this->get('/admin',           'CommunityFusion\Modules\Settings\AdminController@dashboard', $auth);
        $this->get('/admin/settings',  'CommunityFusion\Modules\Settings\AdminController@settings',  $auth);

        // ── Admin Blokken ──────────────────────────────────────────────────
        $this->get('/admin/blocks',                      'CommunityFusion\Modules\Blocks\BlockController@index',         $auth);
        $this->get('/admin/blocks/create',               'CommunityFusion\Modules\Blocks\BlockController@create',        $auth);
        $this->post('/admin/blocks/store',               'CommunityFusion\Modules\Blocks\BlockController@store',         $auth);
        $this->post('/admin/blocks/{id:[0-9]+}/update',  'CommunityFusion\Modules\Blocks\BlockController@update',        $auth);
        $this->post('/admin/blocks/{id:[0-9]+}/delete',  'CommunityFusion\Modules\Blocks\BlockController@delete',        $auth);

        // ── REST API v1 ────────────────────────────────────────────────────
        $this->get('/api/v1/status',                     'CommunityFusion\Api\V1\StatusController@index');
        $this->post('/api/v1/auth/login',                'CommunityFusion\Api\V1\AuthController@login');
        $this->get('/api/v1/blocks/zones',               'CommunityFusion\Modules\Blocks\BlockController@getZonesApi',   $auth);
        $this->post('/api/v1/blocks/positions',          'CommunityFusion\Modules\Blocks\BlockController@savePositions', $auth);

        // ── OAuth Callbacks ────────────────────────────────────────────────
        $this->get('/auth/discord/callback', 'CommunityFusion\Modules\Users\OAuthController@discordCallback');
        $this->get('/auth/twitch/callback',  'CommunityFusion\Modules\Users\OAuthController@twitchCallback');
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: Router.php | Role: Core | Version: 1.1.0                     ║
// ║  Created: 2026-06-06 | Last Updated: 2026-06-06  03:20              ║
// ║  Status: Updated | Notes: Block API + admin routes toegevoegd        ║
// ╚══════════════════════════════════════════════════════════════════════╝

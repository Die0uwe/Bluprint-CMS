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

namespace CommunityFusion\Api\Middleware;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Auth\AuthManager;
use CommunityFusion\Core\Auth\JWTManager;

final class AuthMiddleware
{
    public function __construct(
        private readonly AuthManager $auth,
        private readonly JWTManager  $jwt,
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        // Controleer sessie (web requests)
        if ($this->auth->check()) {
            return $next($request);
        }

        // Controleer Bearer token (API requests)
        $token = $request->bearerToken();
        if ($token !== null) {
            try {
                $payload = $this->jwt->verify($token);
                // User ID is beschikbaar via $payload['sub']
                // Voor nu: redirect naar login (uitbreidbaar)
                return $next($request);
            } catch (\RuntimeException) {
                if ($request->isJson() || $request->isAjax()) {
                    return Response::json(['error' => 'Ongeldige of verlopen token.'], 401);
                }
            }
        }

        // Niet ingelogd
        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['error' => 'Authenticatie vereist.'], 401);
        }

        return Response::redirect('/login?redirect=' . urlencode($request->getPath()));
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : AuthMiddleware.php                                   ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Sessie + JWT check middleware                        ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

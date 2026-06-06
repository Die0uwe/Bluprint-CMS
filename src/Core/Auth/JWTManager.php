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

namespace CommunityFusion\Core\Auth;

/**
 * JWT Manager voor API-authenticatie.
 * Gebruikt HS256 signing. Tokens bevatten user ID en rollen.
 */
final class JWTManager
{
    public function __construct(
        private readonly string $secret,
        private readonly int    $ttl = 3600,
    ) {}

    public function generate(array $payload): string
    {
        $header  = $this->base64url(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->base64url(json_encode(array_merge($payload, [
            'iat' => time(),
            'exp' => time() + $this->ttl,
        ])));
        $signature = $this->base64url(hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true));
        return "{$header}.{$payload}.{$signature}";
    }

    public function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new \RuntimeException('Ongeldig JWT formaat.');

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64url(hash_hmac('sha256', "{$header}.{$payload}", $this->secret, true));

        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('JWT handtekening ongeldig.');
        }

        $data = json_decode($this->base64urlDecode($payload), true);

        if (($data['exp'] ?? 0) < time()) {
            throw new \RuntimeException('JWT is verlopen.');
        }

        return $data;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : JWTManager.php                                       ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : JWT generatie + validatie HS256                      ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

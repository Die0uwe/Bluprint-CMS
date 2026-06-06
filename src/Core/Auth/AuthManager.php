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

use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Auth\RBAC\RBACManager;

/**
 * Authenticatie Manager
 * Beheert login, logout, sessie, en de huidige gebruiker.
 */
final class AuthManager
{
    private ?array $currentUser = null;

    public function __construct(
        private readonly Connection   $db,
        private readonly RBACManager  $rbac,
        private readonly JWTManager   $jwt,
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            $this->startSecureSession();
        }

        // Laad gebruiker uit sessie als die bestaat
        if (isset($_SESSION['user_id'])) {
            $this->currentUser = $this->findUserById((int) $_SESSION['user_id']);
        }
    }

    /**
     * Log een gebruiker in met username/email + wachtwoord.
     */
    public function attempt(string $identifier, string $password): bool
    {
        $user = $this->db->fetchOne(
            "SELECT * FROM cf_users WHERE (username = ? OR email = ?) AND is_active = 1 AND deleted_at IS NULL",
            [$identifier, $identifier]
        );

        if ($user === null) return false;

        if (!password_verify($password, $user['password_hash'])) {
            $this->logFailedAttempt($identifier);
            return false;
        }

        $this->login($user);
        return true;
    }

    /**
     * Sla de gebruiker op in de sessie.
     */
    public function login(array $user): void
    {
        session_regenerate_id(true); // Voorkom session fixation
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['login_time'] = time();
        $this->currentUser      = $user;

        // Update last_login
        $this->db->execute(
            "UPDATE cf_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
            [$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $user['id']]
        );
    }

    /**
     * Log de huidige gebruiker uit.
     */
    public function logout(): void
    {
        $this->currentUser = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Is de huidige bezoeker ingelogd?
     */
    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    /**
     * Geef de ingelogde gebruiker terug.
     */
    public function user(): ?array
    {
        return $this->currentUser;
    }

    /**
     * Geef het ID van de ingelogde gebruiker terug.
     */
    public function id(): ?int
    {
        return $this->currentUser ? (int) $this->currentUser['id'] : null;
    }

    /**
     * Controleer of de gebruiker een permissie heeft.
     */
    public function can(string $permission): bool
    {
        if (!$this->check()) return false;
        return $this->rbac->userCan((int) $this->currentUser['id'], $permission);
    }

    /**
     * Gooi een exception als de gebruiker geen permissie heeft.
     */
    public function authorize(string $permission): void
    {
        if (!$this->can($permission)) {
            throw new \RuntimeException("Toegang geweigerd: '{$permission}' vereist.", 403);
        }
    }

    /**
     * Registreer een nieuwe gebruiker.
     */
    public function register(array $data): int|string
    {
        $hash = password_hash($data['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 3,
        ]);

        return $this->db->insert('users', [
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $hash,
            'display_name'  => $data['display_name'] ?? $data['username'],
            'locale'        => $data['locale'] ?? 'nl',
            'timezone'      => $data['timezone'] ?? 'Europe/Amsterdam',
        ]);
    }

    private function findUserById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM cf_users WHERE id = ? AND is_active = 1 AND deleted_at IS NULL",
            [$id]
        );
    }

    private function logFailedAttempt(string $identifier): void
    {
        // Rate limiting kan hier uitgebreid worden
        // Voorlopig: log naar storage/logs/auth.log
        $log = CF_ROOT . '/storage/logs/auth.log';
        $line = date('Y-m-d H:i:s') . " FAIL identifier={$identifier} ip=" . ($_SERVER['REMOTE_ADDR'] ?? '-') . PHP_EOL;
        file_put_contents($log, $line, FILE_APPEND | LOCK_EX);
    }

    private function startSecureSession(): void
    {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : AuthManager.php                                      ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Login, logout, sessie, argon2id                      ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

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

namespace CommunityFusion\Core\Auth\RBAC;

use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

/**
 * Role-Based Access Control Manager
 * Beheert rollen, permissies en controles per gebruiker.
 * Resultaten worden gecached voor performance.
 */
final class RBACManager
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    /**
     * Controleer of een gebruiker een specifieke permissie heeft.
     */
    public function userCan(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions, true)
            || in_array('*', $permissions, true); // Super admin heeft alles
    }

    /**
     * Geef alle permissies van een gebruiker terug (gecached).
     *
     * @return string[]
     */
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = "rbac.user.{$userId}.permissions";

        return $this->cache->remember($cacheKey, 300, function() use ($userId) {
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT p.name
                 FROM cf_permissions p
                 JOIN cf_role_permissions rp ON rp.permission_id = p.id
                 JOIN cf_user_roles ur ON ur.role_id = rp.role_id
                 WHERE ur.user_id = ?",
                [$userId]
            );
            return array_column($rows, 'name');
        });
    }

    /**
     * Geef alle rollen van een gebruiker terug.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUserRoles(int $userId): array
    {
        return $this->cache->remember("rbac.user.{$userId}.roles", 300, function() use ($userId) {
            return $this->db->fetchAll(
                "SELECT r.* FROM cf_roles r
                 JOIN cf_user_roles ur ON ur.role_id = r.id
                 WHERE ur.user_id = ?
                 ORDER BY r.priority DESC",
                [$userId]
            );
        });
    }

    /**
     * Wijs een rol toe aan een gebruiker.
     */
    public function assignRole(int $userId, int $roleId, ?int $assignedBy = null): void
    {
        $this->db->execute(
            "INSERT IGNORE INTO cf_user_roles (user_id, role_id, assigned_by)
             VALUES (?, ?, ?)",
            [$userId, $roleId, $assignedBy]
        );
        $this->clearUserCache($userId);
    }

    /**
     * Verwijder een rol van een gebruiker.
     */
    public function removeRole(int $userId, int $roleId): void
    {
        $this->db->execute(
            "DELETE FROM cf_user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $roleId]
        );
        $this->clearUserCache($userId);
    }

    /**
     * Controleer of een gebruiker een specifieke rol heeft.
     */
    public function userHasRole(int $userId, string $roleSlug): bool
    {
        $roles = $this->getUserRoles($userId);
        foreach ($roles as $role) {
            if ($role['name'] === $roleSlug) return true;
        }
        return false;
    }

    /**
     * Wis de RBAC cache voor een gebruiker (na rol/permissie wijziging).
     */
    public function clearUserCache(int $userId): void
    {
        $this->cache->delete("rbac.user.{$userId}.permissions");
        $this->cache->delete("rbac.user.{$userId}.roles");
    }

    /**
     * Maak een nieuwe permissie aan.
     */
    public function createPermission(string $name, string $group, string $description = ''): int|string
    {
        return $this->db->insert('permissions', [
            'name'        => $name,
            'group'       => $group,
            'description' => $description,
        ]);
    }

    /**
     * Wijs een permissie toe aan een rol.
     */
    public function grantPermission(int $roleId, int $permissionId): void
    {
        $this->db->execute(
            "INSERT IGNORE INTO cf_role_permissions (role_id, permission_id) VALUES (?, ?)",
            [$roleId, $permissionId]
        );
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : RBACManager.php                                      ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Rollen + permissies met cache                        ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

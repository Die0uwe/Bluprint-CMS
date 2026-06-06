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

namespace CommunityFusion\Modules\Settings;

use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

final class SettingsRepository
{
    private array $loaded = [];

    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $all = $this->getGroup($group);
        return $all[$key] ?? $default;
    }

    public function getGroup(string $group): array
    {
        if (isset($this->loaded[$group])) return $this->loaded[$group];

        $this->loaded[$group] = $this->cache->remember("settings.{$group}", 3600, function() use ($group) {
            $rows   = $this->db->fetchAll(
                "SELECT `key`, `value`, `type` FROM cf_settings WHERE `group` = ?",
                [$group]
            );
            $result = [];
            foreach ($rows as $row) {
                $result[$row['key']] = $this->cast($row['value'], $row['type']);
            }
            return $result;
        });

        return $this->loaded[$group];
    }

    public function set(string $group, string $key, mixed $value): void
    {
        $this->db->execute(
            "INSERT INTO cf_settings (`group`, `key`, `value`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = NOW()",
            [$group, $key, (string) $value]
        );
        $this->cache->delete("settings.{$group}");
        unset($this->loaded[$group]);
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int'  => (int) $value,
            'bool' => $value === '1' || $value === 'true',
            'json' => json_decode($value ?? '{}', true),
            default => $value,
        };
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : SettingsRepository.php                               ║
// ║  Role         : Data                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Site-instellingen met type casting                   ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

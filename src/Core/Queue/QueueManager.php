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

namespace CommunityFusion\Core\Queue;

use CommunityFusion\Core\Database\Connection;

/**
 * Database-gebaseerde Queue Manager.
 * Jobs worden opgeslagen in cf_queue_jobs en verwerkt door de CLI worker.
 */
final class QueueManager
{
    public function __construct(
        private readonly Connection $db,
    ) {
        $this->ensureTable();
    }

    public function push(Job $job): void
    {
        $availableAt = $job->delaySeconds
            ? date('Y-m-d H:i:s', time() + $job->delaySeconds)
            : date('Y-m-d H:i:s');

        $this->db->execute(
            "INSERT INTO cf_queue_jobs (queue, payload, attempts, available_at, created_at)
             VALUES (?, ?, 0, ?, NOW())",
            [
                $job->queue,
                serialize($job),
                $availableAt,
            ]
        );
    }

    public function pop(string $queue = 'default'): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM cf_queue_jobs
             WHERE queue = ? AND reserved_at IS NULL AND available_at <= NOW() AND failed_at IS NULL
             ORDER BY id ASC LIMIT 1",
            [$queue]
        );
    }

    public function markReserved(int $id): void
    {
        $this->db->execute(
            "UPDATE cf_queue_jobs SET reserved_at = NOW(), attempts = attempts + 1 WHERE id = ?",
            [$id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute("DELETE FROM cf_queue_jobs WHERE id = ?", [$id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $this->db->execute(
            "UPDATE cf_queue_jobs SET failed_at = NOW(), last_error = ? WHERE id = ?",
            [$error, $id]
        );
    }

    private function ensureTable(): void
    {
        try {
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS `cf_queue_jobs` (
                    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `queue`        VARCHAR(100) NOT NULL DEFAULT 'default',
                    `payload`      LONGTEXT NOT NULL,
                    `attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    `reserved_at`  DATETIME NULL,
                    `available_at` DATETIME NOT NULL,
                    `failed_at`    DATETIME NULL,
                    `last_error`   TEXT NULL,
                    `created_at`   DATETIME NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_queue_available` (`queue`, `available_at`, `reserved_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable) {
            // Tabel bestaat al of DB niet beschikbaar
        }
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : QueueManager.php                                     ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Database-gebaseerde queue manager                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

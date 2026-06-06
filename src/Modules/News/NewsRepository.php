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

namespace CommunityFusion\Modules\News;

use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

final class NewsRepository
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    public function getPublished(int $limit = 10, int $offset = 0): array
    {
        return $this->cache->remember("news.published.{$limit}.{$offset}", 300, function() use ($limit, $offset) {
            return $this->db->fetchAll(
                "SELECT n.*, u.username, u.display_name, u.avatar_url
                 FROM cf_news n
                 JOIN cf_users u ON u.id = n.author_id
                 WHERE n.status = 'published' AND n.deleted_at IS NULL
                 ORDER BY n.is_sticky DESC, n.published_at DESC
                 LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
        });
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->cache->remember("news.slug.{$slug}", 600, function() use ($slug) {
            return $this->db->fetchOne(
                "SELECT n.*, u.username, u.display_name, u.avatar_url
                 FROM cf_news n
                 JOIN cf_users u ON u.id = n.author_id
                 WHERE n.slug = ? AND n.status = 'published' AND n.deleted_at IS NULL",
                [$slug]
            );
        });
    }

    public function create(array $data): int|string
    {
        $id = $this->db->insert('news', $data);
        $this->cache->clear();
        return $id;
    }

    public function incrementViews(int $id): void
    {
        $this->db->execute("UPDATE cf_news SET views = views + 1 WHERE id = ?", [$id]);
    }

    public function countPublished(): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM cf_news WHERE status = 'published' AND deleted_at IS NULL"
        );
        return (int) ($row['count'] ?? 0);
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : NewsRepository.php                                   ║
// ║  Role         : Data                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Nieuws CRUD + cache-aside                            ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Api\V1;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;

/**
 * ContentController — REST API voor nieuws en pagina's
 */
final class ContentController
{
    public function __construct(private readonly Connection $db) {}

    /** GET /api/v1/news */
    public function news(Request $request): Response
    {
        $limit  = min(50, max(1, (int)$request->query('limit', 10)));
        $offset = max(0, (int)$request->query('offset', 0));

        $items = $this->db->fetchAll(
            "SELECT n.id, n.slug, n.title, n.summary, n.featured_image,
                    n.views, n.published_at, u.username, u.display_name
             FROM cf_news n
             JOIN cf_users u ON u.id = n.author_id
             WHERE n.status = 'published' AND n.deleted_at IS NULL
             ORDER BY n.is_sticky DESC, n.published_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        return Response::json(['data' => $items]);
    }

    /** GET /api/v1/news/{slug} */
    public function newsItem(Request $request): Response
    {
        $slug = $request->param('slug');
        $item = $this->db->fetchOne(
            "SELECT n.*, u.username, u.display_name, u.avatar_url
             FROM cf_news n JOIN cf_users u ON u.id = n.author_id
             WHERE n.slug = ? AND n.status = 'published' AND n.deleted_at IS NULL",
            [$slug]
        );
        if (!$item) return Response::json(['error' => 'Niet gevonden.'], 404);
        return Response::json($item);
    }

    /** GET /api/v1/pages */
    public function pages(Request $request): Response
    {
        $pages = $this->db->fetchAll(
            "SELECT id, slug, title, meta_title, meta_desc, menu_position
             FROM cf_pages WHERE status = 'published' AND deleted_at IS NULL
             ORDER BY menu_position ASC, title ASC"
        );
        return Response::json(['data' => $pages]);
    }

    /** GET /api/v1/blocks/zones — block layout per zone */
    public function blockZones(Request $request): Response
    {
        $blocks = $this->db->fetchAll(
            "SELECT b.id, b.zone, b.position, b.title, b.is_visible, bt.slug as type
             FROM cf_blocks b JOIN cf_block_types bt ON bt.id = b.block_type_id
             WHERE b.is_visible = 1 ORDER BY b.zone, b.position"
        );

        $zones = [];
        foreach ($blocks as $block) {
            $zones[$block['zone']][] = $block;
        }

        return Response::json($zones);
    }
}

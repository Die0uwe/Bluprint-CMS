<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Blocks;

use CommunityFusion\Core\Block\BlockRegistry;
use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Security\CsrfProtection;

/**
 * BlockController
 * Beheert het admin-blokken-scherm én de drag & drop JSON API.
 */
final class BlockController
{
    private const ZONES = [
        'header'        => 'Header',
        'topmenu'       => 'Top Menu',
        'sidebar_left'  => 'Linker Sidebar',
        'content'       => 'Content',
        'sidebar_right' => 'Rechter Sidebar',
        'footer'        => 'Footer',
    ];

    public function __construct(
        private readonly BlockRegistry $registry,
    ) {}

    // ─── ADMIN OVERZICHT ──────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $zones      = self::ZONES;
        $allTypes   = $this->registry->all();
        $placed     = $this->getPlacedByZone();

        ob_start();
        include __DIR__ . '/views/index.php';
        return Response::html(ob_get_clean());
    }

    // ─── CREATE ───────────────────────────────────────────────────────────────

    public function create(Request $request): Response
    {
        $slug = $request->input('type_slug', '');
        $type = $this->registry->find($slug);

        if ($type === null) {
            return Response::json(['error' => "Block type '{$slug}' niet gevonden."], 404);
        }

        ob_start();
        include __DIR__ . '/views/create.php';
        return Response::html(ob_get_clean());
    }

    public function store(Request $request): Response
    {
        CsrfProtection::validateRequest();

        $typeSlug = $request->input('type_slug', '');
        $zone     = $request->input('zone', 'sidebar_right');
        $title    = $request->input('title', '');
        $config   = $request->input('config', []);

        if (!array_key_exists($zone, self::ZONES)) {
            return Response::json(['error' => 'Ongeldige zone.'], 422);
        }

        $type = $this->registry->find($typeSlug);
        if ($type === null) {
            return Response::json(['error' => 'Block type niet gevonden.'], 404);
        }

        // Valideer config
        try {
            $type->validateConfig(is_array($config) ? $config : []);
        } catch (\Throwable $e) {
            return Response::json(['error' => $e->getMessage()], 422);
        }

        // Bepaal volgende positie in deze zone
        $maxPos = $this->registry->db()->fetchOne(
            "SELECT COALESCE(MAX(position), -1) as max_pos FROM cf_blocks WHERE zone = ?",
            [$zone]
        );

        $blockTypeId = $this->registry->db()->fetchOne(
            "SELECT id FROM cf_block_types WHERE slug = ?",
            [$typeSlug]
        );

        if (!$blockTypeId) {
            return Response::json(['error' => 'Block type niet in DB.'], 500);
        }

        $id = $this->registry->createBlock([
            'block_type_id' => (int) $blockTypeId['id'],
            'zone'          => $zone,
            'position'      => (int) ($maxPos['max_pos'] ?? -1) + 1,
            'title'         => $title ?: null,
            'config'        => json_encode($config),
            'is_visible'    => 1,
        ]);

        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['success' => true, 'block_id' => $id]);
        }

        return Response::redirect('/admin/blocks');
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function update(Request $request): Response
    {
        CsrfProtection::validateRequest();

        $id     = (int) $request->param('id');
        $title  = $request->input('title', '');
        $config = $request->input('config', []);
        $vis    = (int) $request->input('is_visible', 1);

        $this->registry->updateBlock($id, [
            'title'      => $title ?: null,
            'config'     => json_encode($config),
            'is_visible' => $vis,
        ]);

        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['success' => true]);
        }

        return Response::redirect('/admin/blocks');
    }

    // ─── DELETE ───────────────────────────────────────────────────────────────

    public function delete(Request $request): Response
    {
        CsrfProtection::validateRequest();
        $id = (int) $request->param('id');
        $this->registry->deleteBlock($id);

        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['success' => true]);
        }

        return Response::redirect('/admin/blocks');
    }

    // ─── DRAG & DROP SAVE (JSON API) ──────────────────────────────────────────

    /**
     * POST /api/v1/blocks/positions
     * Body: { "zone": "sidebar_right", "positions": { "12": 0, "7": 1, "3": 2 } }
     */
    public function savePositions(Request $request): Response
    {
        $zone      = $request->input('zone', '');
        $positions = $request->input('positions', []);

        if (!array_key_exists($zone, self::ZONES) || !is_array($positions)) {
            return Response::json(['error' => 'Ongeldige invoer.'], 422);
        }

        // Cast keys en values naar int
        $clean = [];
        foreach ($positions as $blockId => $pos) {
            $clean[(int) $blockId] = (int) $pos;
        }

        $this->registry->updatePositions($clean, $zone);
        return Response::json(['success' => true, 'updated' => count($clean)]);
    }

    /**
     * GET /api/v1/blocks/zones — geeft alle zones + blocks terug (voor de drag UI)
     */
    public function getZonesApi(Request $request): Response
    {
        $result = [];
        foreach (self::ZONES as $zoneSlug => $zoneLabel) {
            $blocks = $this->registry->getZoneBlocks($zoneSlug);
            $result[$zoneSlug] = [
                'label'  => $zoneLabel,
                'blocks' => array_map(fn($b) => [
                    'id'       => $b['id'],
                    'title'    => $b['title'],
                    'type'     => $b['type_slug'],
                    'position' => $b['position'],
                    'visible'  => (bool) $b['is_visible'],
                ], $blocks),
            ];
        }
        return Response::json($result);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function getPlacedByZone(): array
    {
        $placed = [];
        foreach (self::ZONES as $zone => $_) {
            $placed[$zone] = $this->registry->getZoneBlocks($zone);
        }
        return $placed;
    }

    public function zones(): array { return self::ZONES; }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: BlockController.php | Role: Core | Version: 1.0.0            ║
// ║  Created: 2026-06-06 | Status: New                                  ║
// ╚══════════════════════════════════════════════════════════════════════╝

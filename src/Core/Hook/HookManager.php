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

namespace CommunityFusion\Core\Hook;

/**
 * WordPress-achtig Hook Systeem.
 * Modules kunnen acties en filters registreren om het CMS te extenden.
 *
 * Actions: voer code uit op een bepaald moment (side-effect)
 * Filters: pas een waarde aan voordat hij gebruikt wordt (pure transform)
 */
final class HookManager
{
    /** @var array<string, array<int, callable[]>> */
    private array $actions = [];

    /** @var array<string, array<int, callable[]>> */
    private array $filters = [];

    // ─── ACTIONS ─────────────────────────────────────────────────────────────

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) return;

        ksort($this->actions[$hook]);
        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $cb) {
                $cb(...$args);
            }
        }
    }

    public function removeAction(string $hook, callable $callback, int $priority = 10): void
    {
        foreach ($this->actions[$hook][$priority] ?? [] as $k => $cb) {
            if ($cb === $callback) {
                unset($this->actions[$hook][$priority][$k]);
            }
        }
    }

    public function hasAction(string $hook): bool
    {
        return !empty($this->actions[$hook]);
    }

    // ─── FILTERS ─────────────────────────────────────────────────────────────

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) return $value;

        ksort($this->filters[$hook]);
        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $cb) {
                $value = $cb($value, ...$args);
            }
        }
        return $value;
    }

    public function removeFilter(string $hook, callable $callback, int $priority = 10): void
    {
        foreach ($this->filters[$hook][$priority] ?? [] as $k => $cb) {
            if ($cb === $callback) {
                unset($this->filters[$hook][$priority][$k]);
            }
        }
    }

    public function hasFilter(string $hook): bool
    {
        return !empty($this->filters[$hook]);
    }

    /**
     * Geef een overzicht van alle geregistreerde hooks (debugging).
     */
    public function debug(): array
    {
        return [
            'actions' => array_map(fn($p) => array_map('count', $p), $this->actions),
            'filters' => array_map(fn($p) => array_map('count', $p), $this->filters),
        ];
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : HookManager.php                                      ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : WordPress-achtig action/filter systeem               ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

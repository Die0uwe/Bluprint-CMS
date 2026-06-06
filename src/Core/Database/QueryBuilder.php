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

namespace CommunityFusion\Core\Database;

/**
 * Fluent Query Builder
 * Genereert veilige prepared statements via method chaining.
 *
 * Voorbeeld:
 *   $db->table('users')
 *      ->select('id', 'username', 'email')
 *      ->where('is_active', 1)
 *      ->where('deleted_at IS NULL')
 *      ->orderBy('created_at', 'DESC')
 *      ->limit(10)
 *      ->get();
 */
final class QueryBuilder
{
    private array $selects    = ['*'];
    private array $wheres     = [];
    private array $bindings   = [];
    private array $orderBys   = [];
    private ?int  $limitVal   = null;
    private ?int  $offsetVal  = null;
    private array $joins      = [];

    public function __construct(
        private readonly Connection $db,
        private readonly string     $table,
    ) {}

    public function select(string ...$columns): static
    {
        $this->selects = $columns;
        return $this;
    }

    public function where(string $column, mixed $value = null, string $operator = '='): static
    {
        if ($value === null) {
            // Ruwe conditie: where('deleted_at IS NULL')
            $this->wheres[] = $column;
        } else {
            $this->wheres[]   = "`{$column}` {$operator} ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $placeholders   = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "`{$column}` IN ({$placeholders})";
        $this->bindings = [...$this->bindings, ...$values];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction        = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBys[] = "`{$column}` {$direction}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitVal = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetVal = $offset;
        return $this;
    }

    public function join(string $table, string $on, string $type = 'INNER'): static
    {
        $this->joins[] = "{$type} JOIN `{$table}` ON {$on}";
        return $this;
    }

    // ─── EXECUTIE ────────────────────────────────────────────────────────────

    public function get(): array
    {
        return $this->db->fetchAll($this->toSql(), $this->bindings);
    }

    public function first(): ?array
    {
        $this->limit(1);
        return $this->db->fetchOne($this->toSql(), $this->bindings);
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $row = $this->db->fetchOne($this->toSql(), $this->bindings);
        return (int) ($row['count'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    // ─── SQL GENERATIE ────────────────────────────────────────────────────────

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->selects);
        $sql .= " FROM `{$this->table}`";

        foreach ($this->joins as $join) {
            $sql .= " {$join}";
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBys)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBys);
        }

        if ($this->limitVal !== null) {
            $sql .= " LIMIT {$this->limitVal}";
        }

        if ($this->offsetVal !== null) {
            $sql .= " OFFSET {$this->offsetVal}";
        }

        return $sql;
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : QueryBuilder.php                                     ║
// ║  Role         : Data                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : Fluent query builder                                 ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

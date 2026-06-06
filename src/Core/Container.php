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

namespace CommunityFusion\Core;

use Psr\Container\ContainerInterface;

/**
 * Eenvoudige PSR-11 Dependency Injection Container.
 * Ondersteunt: singleton, binding, instances en auto-resolve.
 */
final class Container implements ContainerInterface
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, callable> */
    private array $singletons = [];

    /**
     * Registreer een binding (elke keer nieuw object).
     */
    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Registreer een singleton (één instantie per container).
     */
    public function singleton(string $abstract, callable $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }

    /**
     * Sla een bestaande instantie op.
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Maak of haal een service op.
     */
    public function make(string $abstract): mixed
    {
        // Al bestaande instantie
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Singleton: maak één keer en bewaar
        if (isset($this->singletons[$abstract])) {
            $instance = ($this->singletons[$abstract])($this);
            $this->instances[$abstract] = $instance;
            return $instance;
        }

        // Binding: maak elke keer nieuw
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        // Auto-resolve via reflection als het een bestaande class is
        if (class_exists($abstract)) {
            return $this->autoResolve($abstract);
        }

        throw new \RuntimeException("Kan '{$abstract}' niet resolven uit de container.");
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->singletons[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    /**
     * Auto-resolve een class via constructor injection.
     */
    private function autoResolve(string $class): mixed
    {
        $ref         = new \ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if ($constructor === null) {
            return $ref->newInstance();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $params[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    "Kan parameter '\${$param->getName()}' niet resolven voor {$class}."
                );
            }
        }

        return $ref->newInstanceArgs($params);
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║                         FILE CARD                                    ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  File         : Container.php                                        ║
// ║  Role         : Core                                                 ║
// ║  Version      : 1.0.0                                                ║
// ║  Created      : 2026-06-06                                           ║
// ║  Last Updated : 2026-06-06  03:00                                    ║
// ║  Status       : New                                                  ║
// ║  Notes        : PSR-11 Dependency Injection Container                ║
// ╠══════════════════════════════════════════════════════════════════════╣
// ║  Created by Dieouwe                                                  ║
// ║  🌐 www.dieouwe.nl          ⚔️  www.slayeralliance.com              ║
// ║  📦 curseforge.com/members/dieouwe/projects                         ║
// ║  💬 discord.gg/y8Pu5qsEbQ                                           ║
// ╚══════════════════════════════════════════════════════════════════════╝

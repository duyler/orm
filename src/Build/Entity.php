<?php

declare(strict_types=1);

namespace Duyler\ORM\Build;

use Duyler\ORM\SchemaCollector;

final class Entity
{
    private static SchemaCollector $collector;

    private static string $entityClass;

    public function __construct(
        SchemaCollector $collector,
        string $entityClass = '',
    ) {
        self::$collector = $collector;
        self::$entityClass = $entityClass;
    }

    public static function create(string $entity): self
    {
        self::$entityClass = $entity;
        self::$collector->addSchema(self::$entityClass);
        return new self(
            self::$collector,
            $entity,
        );
    }

    public function database(string $database): self
    {
        self::$collector->addDatabase(self::$entityClass, $database);
        return $this;
    }

    public function table(string $table): self
    {
        self::$collector->addTable(self::$entityClass, $table);
        return $this;
    }

    public function repository(string $repositoryClass): self
    {
        self::$collector->addRepository(self::$entityClass, $repositoryClass);
        return $this;
    }

    public function primaryKey(string $primaryKey): self
    {
        self::$collector->addPrimaryKey(self::$entityClass, $primaryKey);
        return $this;
    }

    public function columns(array $columns): self
    {
        self::$collector->addColumns(self::$entityClass, $columns);
        return $this;
    }

    public function relations(array $relations): self
    {
        self::$collector->addRelations(self::$entityClass, $relations);
        return $this;
    }

    public function typecast(array $typecast): self
    {
        self::$collector->addTypecast(self::$entityClass, $typecast);
        return $this;
    }
}

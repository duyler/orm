<?php

declare(strict_types=1);

namespace Duyler\ORM;

use Cycle\ORM\Mapper\Mapper;
use Cycle\ORM\Schema;

class SchemaCollector
{
    /** @var array<string, array<int, mixed>> $schema */
    private array $schema = [];

    public function addSchema(string $entityClass): void
    {
        $this->schema[$entityClass] = [Schema::MAPPER => Mapper::class];
    }

    public function addDatabase(string $entityClass, string $database): void
    {
        $this->schema[$entityClass][Schema::DATABASE] = $database;
    }

    public function addTable(string $entityClass, string $table): void
    {
        $this->schema[$entityClass][Schema::TABLE] = $table;
    }

    public function addPrimaryKey(string $entityClass, string $primaryKey): void
    {
        $this->schema[$entityClass][Schema::PRIMARY_KEY] = $primaryKey;
    }

    public function addColumns(string $entityClass, array $columns): void
    {
        $this->schema[$entityClass][Schema::COLUMNS] = $columns;
    }

    public function addRelations(string $entityClass, array $relations): void
    {
        $this->schema[$entityClass][Schema::RELATIONS] = $relations;
    }

    public function addTypecast(string $entityClass, array $typecast): void
    {
        $this->schema[$entityClass][Schema::TYPECAST] = $typecast;
    }

    public function addRepository(string $entityClass, string $repositoryClass): void
    {
        $this->schema[$entityClass][Schema::REPOSITORY] = $repositoryClass;
    }

    public function addTypecastHandler(string $entityClass, array $typecastHandler): void
    {
        $this->schema[$entityClass][Schema::TYPECAST_HANDLER] = $typecastHandler;
    }

    public function getSchema(): ORMSchema
    {
        return new ORMSchema($this->schema);
    }
}

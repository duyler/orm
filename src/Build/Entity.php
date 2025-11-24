<?php

declare(strict_types=1);

namespace Duyler\ORM\Build;

use Duyler\ORM\SchemaCollector;

final class Entity
{
    private static SchemaCollector $collector;

    /** @var non-empty-string */
    private static string $entityClass;

    public function __construct(
        SchemaCollector $collector,
        string $entityClass = '',
    ) {
        self::$collector = $collector;
        if ($entityClass !== '') {
            self::$entityClass = $entityClass;
        }
    }

    public static function declare(string $entity): self
    {
        assert($entity !== '', 'Entity class name cannot be empty');
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

    public function typecastHandler(array $typecastHandler): self
    {
        self::$collector->addTypecastHandler(self::$entityClass, $typecastHandler);
        return $this;
    }

    /**
     * Add custom listeners to entity
     *
     * @param non-empty-array<int, class-string|array{class: class-string, args: array<string, mixed>}> $listeners
     * @return self
     */
    public function listeners(array $listeners): self
    {
        self::$collector->addListeners(self::$entityClass, $listeners);
        return $this;
    }

    /**
     * Automatically set creation timestamp
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @return self
     */
    public function createdAt(string $field = 'createdAt', ?string $column = null): self
    {
        $listener = [
            'class' => \Cycle\ORM\Entity\Behavior\Listener\CreatedAt::class,
            'args' => ['field' => $field],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }

    /**
     * Automatically update timestamp on entity change
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @param bool $nullable Whether field is nullable
     * @return self
     */
    public function updatedAt(
        string $field = 'updatedAt',
        ?string $column = null,
        bool $nullable = false,
    ): self {
        $listener = [
            'class' => \Cycle\ORM\Entity\Behavior\Listener\UpdatedAt::class,
            'args' => [
                'field' => $field,
                'nullable' => $nullable,
            ],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }

    /**
     * Enable soft delete for entity
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @return self
     */
    public function softDelete(string $field = 'deletedAt', ?string $column = null): self
    {
        $listener = [
            'class' => \Cycle\ORM\Entity\Behavior\Listener\SoftDelete::class,
            'args' => ['field' => $field],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }
}

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

    /**
     * Enable optimistic locking
     *
     * Available rules:
     * - 'increment': auto-incrementing integer (default for int fields)
     * - 'microtime': microtime string (default for string fields)
     * - 'random-string': random string
     * - 'datetime': datetime version (default for datetime fields)
     * - 'manual': manual version management
     *
     * @param non-empty-string $field Version property name
     * @param non-empty-string|null $column Version column name (auto-detect if null)
     * @param non-empty-string|null $rule Lock strategy
     * @return self
     */
    public function optimisticLock(
        string $field = 'version',
        ?string $column = null,
        ?string $rule = null,
    ): self {
        $listener = [
            'class' => \Cycle\ORM\Entity\Behavior\Listener\OptimisticLock::class,
            'args' => [
                'field' => $field,
                'rule' => $rule,
            ],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }

    /**
     * Add custom hook for entity events
     *
     * Available events:
     * - \Cycle\ORM\Entity\Behavior\Event\Mapper\Command\OnCreate::class
     * - \Cycle\ORM\Entity\Behavior\Event\Mapper\Command\OnUpdate::class
     * - \Cycle\ORM\Entity\Behavior\Event\Mapper\Command\OnDelete::class
     *
     * @param callable $callable Callback function
     * @param class-string|non-empty-array<int, class-string> $events Event class name(s) to listen
     * @return self
     */
    public function hook(callable $callable, string|array $events): self
    {
        $listener = [
            'class' => \Cycle\ORM\Entity\Behavior\Listener\Hook::class,
            'args' => [
                'callable' => $callable,
                'events' => is_string($events) ? [$events] : $events,
            ],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }

    /**
     * Add custom event listener with DI support
     *
     * @param class-string $listenerClass Listener class name
     * @param array<string, mixed> $args Additional constructor arguments
     * @return self
     */
    public function eventListener(string $listenerClass, array $args = []): self
    {
        $listener = $args === []
            ? $listenerClass
            : ['class' => $listenerClass, 'args' => $args];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }

    /**
     * Automatically generate UUID v1 (time-based)
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @param bool $nullable Generate UUID or not
     * @return self
     */
    public function uuid1(
        string $field = 'uuid',
        ?string $column = null,
        bool $nullable = false,
    ): self {
        return $this->addUuidListener(
            \Cycle\ORM\Entity\Behavior\Uuid\Listener\Uuid1::class,
            $field,
            $nullable,
        );
    }

    /**
     * Automatically generate UUID v4 (random)
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @param bool $nullable Generate UUID or not
     * @return self
     */
    public function uuid4(
        string $field = 'uuid',
        ?string $column = null,
        bool $nullable = false,
    ): self {
        return $this->addUuidListener(
            \Cycle\ORM\Entity\Behavior\Uuid\Listener\Uuid4::class,
            $field,
            $nullable,
        );
    }

    /**
     * Automatically generate UUID v7 (Unix Epoch Time) - RECOMMENDED
     *
     * @param non-empty-string $field Property name
     * @param non-empty-string|null $column Column name (auto-detect if null)
     * @param bool $nullable Generate UUID or not
     * @return self
     */
    public function uuid7(
        string $field = 'uuid',
        ?string $column = null,
        bool $nullable = false,
    ): self {
        return $this->addUuidListener(
            \Cycle\ORM\Entity\Behavior\Uuid\Listener\Uuid7::class,
            $field,
            $nullable,
        );
    }

    /**
     * Helper method to add UUID listener
     *
     * @param class-string $listenerClass
     * @param non-empty-string $field
     * @param bool $nullable
     * @return self
     */
    private function addUuidListener(
        string $listenerClass,
        string $field,
        bool $nullable,
    ): self {
        $listener = [
            'class' => $listenerClass,
            'args' => [
                'field' => $field,
                'nullable' => $nullable,
            ],
        ];

        self::$collector->addListeners(self::$entityClass, [$listener]);
        return $this;
    }
}

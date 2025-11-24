<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Cycle\ORM\Schema;
use Duyler\ORM\Build\Entity;
use Duyler\ORM\SchemaCollector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EntityBehaviorsTest extends TestCase
{
    private SchemaCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new SchemaCollector();
        new Entity($this->collector);
    }

    #[Test]
    public function listeners_should_add_custom_listeners(): void
    {
        Entity::declare('TestEntity')
            ->listeners([
                'SimpleListener',
                ['class' => 'ComplexListener', 'args' => ['key' => 'value']],
            ]);

        $schema = $this->collector->getSchema()->toArray();

        $this->assertCount(2, $schema['TestEntity'][Schema::LISTENERS]);
        $this->assertEquals('SimpleListener', $schema['TestEntity'][Schema::LISTENERS][0]);
        $this->assertEquals('ComplexListener', $schema['TestEntity'][Schema::LISTENERS][1]['class']);
    }

    #[Test]
    public function created_at_behavior_should_add_listener(): void
    {
        Entity::declare('TestEntity')
            ->createdAt();

        $schema = $this->collector->getSchema()->toArray();

        $this->assertArrayHasKey(Schema::LISTENERS, $schema['TestEntity']);
        $this->assertCount(1, $schema['TestEntity'][Schema::LISTENERS]);

        $listener = $schema['TestEntity'][Schema::LISTENERS][0];
        $this->assertEquals(
            \Cycle\ORM\Entity\Behavior\Listener\CreatedAt::class,
            $listener['class'],
        );
        $this->assertEquals('createdAt', $listener['args']['field']);
    }

    #[Test]
    public function created_at_with_custom_field_should_use_custom_field(): void
    {
        Entity::declare('TestEntity')
            ->createdAt(field: 'customCreatedAt');

        $schema = $this->collector->getSchema()->toArray();
        $listener = $schema['TestEntity'][Schema::LISTENERS][0];

        $this->assertEquals('customCreatedAt', $listener['args']['field']);
    }

    #[Test]
    public function updated_at_behavior_should_add_listener(): void
    {
        Entity::declare('TestEntity')
            ->updatedAt();

        $schema = $this->collector->getSchema()->toArray();

        $this->assertArrayHasKey(Schema::LISTENERS, $schema['TestEntity']);
        $listener = $schema['TestEntity'][Schema::LISTENERS][0];

        $this->assertEquals(
            \Cycle\ORM\Entity\Behavior\Listener\UpdatedAt::class,
            $listener['class'],
        );
        $this->assertEquals('updatedAt', $listener['args']['field']);
        $this->assertFalse($listener['args']['nullable']);
    }

    #[Test]
    public function updated_at_with_nullable_should_set_nullable(): void
    {
        Entity::declare('TestEntity')
            ->updatedAt(nullable: true);

        $schema = $this->collector->getSchema()->toArray();
        $listener = $schema['TestEntity'][Schema::LISTENERS][0];

        $this->assertTrue($listener['args']['nullable']);
    }

    #[Test]
    public function soft_delete_behavior_should_add_listener(): void
    {
        Entity::declare('TestEntity')
            ->softDelete();

        $schema = $this->collector->getSchema()->toArray();
        $listener = $schema['TestEntity'][Schema::LISTENERS][0];

        $this->assertEquals(
            \Cycle\ORM\Entity\Behavior\Listener\SoftDelete::class,
            $listener['class'],
        );
        $this->assertEquals('deletedAt', $listener['args']['field']);
    }

    #[Test]
    public function soft_delete_with_custom_field_should_use_custom_field(): void
    {
        Entity::declare('TestEntity')
            ->softDelete(field: 'removed_at');

        $schema = $this->collector->getSchema()->toArray();
        $listener = $schema['TestEntity'][Schema::LISTENERS][0];

        $this->assertEquals('removed_at', $listener['args']['field']);
    }

    #[Test]
    public function multiple_behaviors_should_add_multiple_listeners(): void
    {
        Entity::declare('TestEntity')
            ->createdAt()
            ->updatedAt()
            ->softDelete();

        $schema = $this->collector->getSchema()->toArray();

        $this->assertCount(3, $schema['TestEntity'][Schema::LISTENERS]);
    }

    #[Test]
    public function behaviors_can_be_chained_with_other_methods(): void
    {
        Entity::declare('TestEntity')
            ->database('default')
            ->table('test')
            ->primaryKey('id')
            ->createdAt()
            ->updatedAt();

        $schema = $this->collector->getSchema()->toArray();

        $this->assertEquals('default', $schema['TestEntity'][Schema::DATABASE]);
        $this->assertEquals('test', $schema['TestEntity'][Schema::TABLE]);
        $this->assertEquals('id', $schema['TestEntity'][Schema::PRIMARY_KEY]);
        $this->assertCount(2, $schema['TestEntity'][Schema::LISTENERS]);
    }
}

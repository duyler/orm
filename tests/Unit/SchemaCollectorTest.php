<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Duyler\ORM\SchemaCollector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaCollectorTest extends TestCase
{
    private SchemaCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new SchemaCollector();
    }

    #[Test]
    public function add_schema_should_add_entity(): void
    {
        $this->collector->addSchema('TestEntity');
        $schema = $this->collector->getSchema()->toArray();
        $this->assertArrayHasKey('TestEntity', $schema);
    }

    #[Test]
    public function add_database_should_set_database(): void
    {
        $this->collector->addSchema('TestEntity');
        $this->collector->addDatabase('TestEntity', 'test_db');
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals('test_db', $schema['TestEntity'][\Cycle\ORM\Schema::DATABASE]);
    }

    #[Test]
    public function add_table_should_set_table(): void
    {
        $this->collector->addSchema('TestEntity');
        $this->collector->addTable('TestEntity', 'test_table');
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals('test_table', $schema['TestEntity'][\Cycle\ORM\Schema::TABLE]);
    }

    #[Test]
    public function add_primary_key_should_set_primary_key(): void
    {
        $this->collector->addSchema('TestEntity');
        $this->collector->addPrimaryKey('TestEntity', 'id');
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals('id', $schema['TestEntity'][\Cycle\ORM\Schema::PRIMARY_KEY]);
    }

    #[Test]
    public function add_columns_should_set_columns(): void
    {
        $this->collector->addSchema('TestEntity');
        $columns = ['id' => 'int', 'name' => 'string'];
        $this->collector->addColumns('TestEntity', $columns);
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals($columns, $schema['TestEntity'][\Cycle\ORM\Schema::COLUMNS]);
    }

    #[Test]
    public function add_relations_should_set_relations(): void
    {
        $this->collector->addSchema('TestEntity');
        $relations = ['user' => ['type' => 'hasOne']];
        $this->collector->addRelations('TestEntity', $relations);
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals($relations, $schema['TestEntity'][\Cycle\ORM\Schema::RELATIONS]);
    }

    #[Test]
    public function add_typecast_should_set_typecast(): void
    {
        $this->collector->addSchema('TestEntity');
        $typecast = ['id' => 'uuid'];
        $this->collector->addTypecast('TestEntity', $typecast);
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals($typecast, $schema['TestEntity'][\Cycle\ORM\Schema::TYPECAST]);
    }

    #[Test]
    public function add_repository_should_set_repository(): void
    {
        $this->collector->addSchema('TestEntity');
        $this->collector->addRepository('TestEntity', 'TestRepository');
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals('TestRepository', $schema['TestEntity'][\Cycle\ORM\Schema::REPOSITORY]);
    }

    #[Test]
    public function add_typecast_handler_should_set_typecast_handler(): void
    {
        $this->collector->addSchema('TestEntity');
        $handler = ['handler' => 'test'];
        $this->collector->addTypecastHandler('TestEntity', $handler);
        $schema = $this->collector->getSchema()->toArray();
        $this->assertEquals($handler, $schema['TestEntity'][\Cycle\ORM\Schema::TYPECAST_HANDLER]);
    }
}

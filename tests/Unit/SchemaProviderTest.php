<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Cycle\ORM\Schema;
use Duyler\DI\ContainerService;
use Duyler\ORM\ORMSchema;
use Duyler\ORM\Provider\SchemaProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaProviderTest extends TestCase
{
    #[Test]
    public function should_create_schema_from_orm_schema(): void
    {
        $schemaData = [
            'entity' => [
                'role' => 'entity',
                'table' => 'users',
            ],
        ];

        $ormSchema = new ORMSchema($schemaData);
        $provider = new SchemaProvider($ormSchema);

        $containerService = $this->createMock(ContainerService::class);
        $result = $provider->factory($containerService);

        $this->assertInstanceOf(Schema::class, $result);
    }

    #[Test]
    public function should_create_schema_with_empty_data(): void
    {
        $ormSchema = new ORMSchema([]);
        $provider = new SchemaProvider($ormSchema);

        $containerService = $this->createMock(ContainerService::class);
        $result = $provider->factory($containerService);

        $this->assertInstanceOf(Schema::class, $result);
    }

    #[Test]
    public function should_create_new_schema_instance_on_each_call(): void
    {
        $schemaData = [
            'user' => [
                Schema::ROLE => 'entity',
                Schema::DATABASE => 'default',
                Schema::TABLE => 'users',
            ],
        ];

        $ormSchema = new ORMSchema($schemaData);
        $provider = new SchemaProvider($ormSchema);

        $containerService = $this->createMock(ContainerService::class);

        $schema1 = $provider->factory($containerService);
        $schema2 = $provider->factory($containerService);

        $this->assertInstanceOf(Schema::class, $schema1);
        $this->assertInstanceOf(Schema::class, $schema2);
        $this->assertNotSame($schema1, $schema2);
    }

    #[Test]
    public function should_create_schema_with_complex_data(): void
    {
        $schemaData = [
            'user' => [
                Schema::ROLE => 'entity',
                Schema::DATABASE => 'default',
                Schema::TABLE => 'users',
                Schema::PRIMARY_KEY => 'id',
                Schema::COLUMNS => [
                    'id' => 'id',
                    'name' => 'name',
                    'email' => 'email',
                ],
                Schema::TYPECAST => [
                    'id' => 'int',
                    'name' => 'string',
                    'email' => 'string',
                ],
            ],
            'post' => [
                Schema::ROLE => 'entity',
                Schema::DATABASE => 'default',
                Schema::TABLE => 'posts',
                Schema::PRIMARY_KEY => 'id',
            ],
        ];

        $ormSchema = new ORMSchema($schemaData);
        $provider = new SchemaProvider($ormSchema);

        $containerService = $this->createMock(ContainerService::class);
        $result = $provider->factory($containerService);

        $this->assertInstanceOf(Schema::class, $result);
    }
}

<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Duyler\ORM\ORMSchema;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ORMSchemaTest extends TestCase
{
    #[Test]
    public function should_create_with_empty_schema(): void
    {
        $schema = new ORMSchema();

        $this->assertEquals([], $schema->toArray());
    }

    #[Test]
    public function should_create_with_schema_data(): void
    {
        $data = [
            'entity' => [
                'role' => 'entity',
                'table' => 'users',
            ],
        ];

        $schema = new ORMSchema($data);

        $this->assertEquals($data, $schema->toArray());
    }

    #[Test]
    public function should_return_same_array_on_multiple_calls(): void
    {
        $data = ['test' => 'value'];
        $schema = new ORMSchema($data);

        $result1 = $schema->toArray();
        $result2 = $schema->toArray();

        $this->assertSame($result1, $result2);
        $this->assertEquals($data, $result1);
    }

    #[Test]
    public function should_handle_nested_schema_data(): void
    {
        $data = [
            'entity1' => [
                'role' => 'entity',
                'table' => 'users',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            'entity2' => [
                'role' => 'entity',
                'table' => 'posts',
            ],
        ];

        $schema = new ORMSchema($data);

        $this->assertEquals($data, $schema->toArray());
    }

    #[Test]
    public function should_be_readonly_and_immutable(): void
    {
        $originalData = ['test' => 'value'];
        $schema = new ORMSchema($originalData);

        $result = $schema->toArray();
        $result['test'] = 'modified';

        $this->assertEquals($originalData, $schema->toArray());
    }
}

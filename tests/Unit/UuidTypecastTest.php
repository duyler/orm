<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Duyler\ORM\Typecast\UuidTypecast;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidTypecastTest extends TestCase
{
    private $database;
    private UuidTypecast $typecast;

    protected function setUp(): void
    {
        $this->database = $this->createMock(\Cycle\Database\DatabaseInterface::class);
        $this->typecast = new UuidTypecast($this->database);
    }

    #[Test]
    public function set_rules_should_extract_uuid_rules(): void
    {
        $rules = [
            'id' => 'uuid',
            'name' => 'string',
        ];
        $result = $this->typecast->setRules($rules);
        $this->assertArrayNotHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
    }

    #[Test]
    public function cast_should_convert_string_to_uuid(): void
    {
        $this->typecast->setRules(['id' => 'uuid']);
        $uuid = Uuid::uuid4()->toString();
        $data = ['id' => $uuid];
        $result = $this->typecast->cast($data);
        $this->assertInstanceOf(UuidInterface::class, $result['id']);
        $this->assertEquals($uuid, $result['id']->toString());
    }

    #[Test]
    public function uncast_should_convert_uuid_to_string(): void
    {
        $this->typecast->setRules(['id' => 'uuid']);
        $uuid = Uuid::uuid4();
        $data = ['id' => $uuid];
        $result = $this->typecast->uncast($data);
        $this->assertIsString($result['id']);
        $this->assertEquals($uuid->toString(), $result['id']);
    }
} 

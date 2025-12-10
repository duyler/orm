<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Cycle\Database\Config\SQLiteDriverConfig;
use Duyler\ORM\DBALConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DBALConfigTest extends TestCase
{
    #[Test]
    public function should_create_with_default_values(): void
    {
        $config = new DBALConfig();

        $this->assertEquals('default', $config->default);
        $this->assertNull($config->logger);
        $this->assertEquals([], $config->aliases);
        $this->assertEquals([], $config->databases);
        $this->assertEquals([], $config->connections);
        $this->assertTrue($config->autocommit);
    }

    #[Test]
    public function should_create_with_custom_default(): void
    {
        $config = new DBALConfig(default: 'primary');

        $this->assertEquals('primary', $config->default);
    }

    #[Test]
    public function should_create_with_logger(): void
    {
        $loggerClass = 'App\\Logger\\CustomLogger';
        $config = new DBALConfig(logger: $loggerClass);

        $this->assertEquals($loggerClass, $config->logger);
    }

    #[Test]
    public function should_create_with_aliases(): void
    {
        $aliases = ['primary' => 'default', 'secondary' => 'backup'];
        $config = new DBALConfig(aliases: $aliases);

        $this->assertEquals($aliases, $config->aliases);
    }

    #[Test]
    public function should_create_with_databases(): void
    {
        $databases = [
            'default' => ['connection' => 'sqlite'],
            'backup' => ['connection' => 'mysql'],
        ];
        $config = new DBALConfig(databases: $databases);

        $this->assertEquals($databases, $config->databases);
    }

    #[Test]
    public function should_create_with_connections(): void
    {
        $connection = $this->createMock(SQLiteDriverConfig::class);
        $connections = ['sqlite' => $connection];

        $config = new DBALConfig(connections: $connections);

        $this->assertSame($connections, $config->connections);
        $this->assertSame($connection, $config->connections['sqlite']);
    }

    #[Test]
    public function should_create_with_autocommit_disabled(): void
    {
        $config = new DBALConfig(autocommit: false);

        $this->assertFalse($config->autocommit);
    }

    #[Test]
    public function should_create_with_all_parameters(): void
    {
        $connection = $this->createMock(SQLiteDriverConfig::class);

        $config = new DBALConfig(
            default: 'primary',
            logger: 'App\\Logger',
            aliases: ['db' => 'primary'],
            databases: ['primary' => ['connection' => 'sqlite']],
            connections: ['sqlite' => $connection],
            autocommit: false,
        );

        $this->assertEquals('primary', $config->default);
        $this->assertEquals('App\\Logger', $config->logger);
        $this->assertEquals(['db' => 'primary'], $config->aliases);
        $this->assertEquals(['primary' => ['connection' => 'sqlite']], $config->databases);
        $this->assertSame(['sqlite' => $connection], $config->connections);
        $this->assertFalse($config->autocommit);
    }

    #[Test]
    public function should_be_readonly(): void
    {
        $config = new DBALConfig(default: 'test');

        $this->assertEquals('test', $config->default);
    }
}

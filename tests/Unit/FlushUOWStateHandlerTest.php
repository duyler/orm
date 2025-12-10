<?php

declare(strict_types=1);

namespace Duyler\ORM\Test\Unit;

use Cycle\ORM\EntityManagerInterface;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Duyler\ORM\DBALConfig;
use Duyler\ORM\State\FlushUOWStateHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FlushUOWStateHandlerTest extends TestCase
{
    #[Test]
    public function should_run_and_clean_entity_manager_when_autocommit_enabled(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $config = new DBALConfig(autocommit: true);

        $container->expects($this->once())
            ->method('get')
            ->with(EntityManagerInterface::class)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('run');

        $entityManager->expects($this->once())
            ->method('clean')
            ->with(true);

        $handler = new FlushUOWStateHandler($container, $config);

        $stateService = $this->createMock(StateMainEmptyService::class);
        $stateContext = $this->createMock(StateContext::class);

        $handler->handle($stateService, $stateContext);
    }

    #[Test]
    public function should_only_clean_entity_manager_when_autocommit_disabled(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $config = new DBALConfig(autocommit: false);

        $container->expects($this->once())
            ->method('get')
            ->with(EntityManagerInterface::class)
            ->willReturn($entityManager);

        $entityManager->expects($this->never())
            ->method('run');

        $entityManager->expects($this->once())
            ->method('clean')
            ->with(true);

        $handler = new FlushUOWStateHandler($container, $config);

        $stateService = $this->createMock(StateMainEmptyService::class);
        $stateContext = $this->createMock(StateContext::class);

        $handler->handle($stateService, $stateContext);
    }

    #[Test]
    public function should_get_entity_manager_from_container(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $config = new DBALConfig(autocommit: true);

        $container->expects($this->once())
            ->method('get')
            ->with(EntityManagerInterface::class)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('run');

        $entityManager->expects($this->once())
            ->method('clean')
            ->with(true);

        $handler = new FlushUOWStateHandler($container, $config);

        $stateService = $this->createMock(StateMainEmptyService::class);
        $stateContext = $this->createMock(StateContext::class);

        $handler->handle($stateService, $stateContext);
    }

    #[Test]
    public function should_always_clean_with_true_parameter(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $config = new DBALConfig(autocommit: false);

        $container->method('get')
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('clean')
            ->with($this->identicalTo(true));

        $handler = new FlushUOWStateHandler($container, $config);

        $stateService = $this->createMock(StateMainEmptyService::class);
        $stateContext = $this->createMock(StateContext::class);

        $handler->handle($stateService, $stateContext);
    }

    #[Test]
    public function should_handle_default_autocommit_value(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $config = new DBALConfig(); // autocommit defaults to true

        $container->method('get')
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('run');

        $entityManager->expects($this->once())
            ->method('clean')
            ->with(true);

        $handler = new FlushUOWStateHandler($container, $config);

        $stateService = $this->createMock(StateMainEmptyService::class);
        $stateContext = $this->createMock(StateContext::class);

        $handler->handle($stateService, $stateContext);
    }
}

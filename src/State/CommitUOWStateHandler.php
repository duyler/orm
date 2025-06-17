<?php

declare(strict_types=1);

namespace Duyler\ORM\State;

use Cycle\ORM\EntityManagerInterface;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Duyler\ORM\DBALConfig;
use Override;

class CommitUOWStateHandler implements MainEmptyStateHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private DBALConfig $dbalConfig,
    ) {}

    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
    {
        if ($this->dbalConfig->autocommit) {
            /** @var EntityManagerInterface $em */
            $em = $this->container->get(EntityManagerInterface::class);
            $em->run();
        }
    }
}

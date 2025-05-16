<?php

declare(strict_types=1);

namespace Duyler\ORM\Fixture;

use Cycle\ORM\ORMInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class FixtureLoadCommandHandler
{
    public function __construct(
        private FixtureConfig $config,
        private ORMInterface $orm,
        private ContainerInterface $container,
    ) {}

    public function __invoke()
    {
        $answer = readline('Are you sure you want to load data fixtures into database? (y/n): ');

        if ('y' !== strtolower((string) $answer)) {
            throw new RuntimeException('Aborted!');
        }

        /** @var class-string<FixtureInterface> $fixtureClass */
        foreach ($this->config->fixtures as $fixtureClass) {

            if (!class_exists($fixtureClass)) {
                throw new InvalidArgumentException("Fixture class $fixtureClass does not exist");
            }

            /** @var FixtureInterface $fixture */
            $fixture = $this->container->get($fixtureClass);
            if (false === $fixture instanceof FixtureInterface) {
                throw new InvalidArgumentException('Fixture class must implement ' . FixtureInterface::class);
            }

            $fixture->load($this->orm);
        }
    }
}

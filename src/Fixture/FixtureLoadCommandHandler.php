<?php

declare(strict_types=1);

namespace Duyler\ORM\Fixture;

use Cycle\ORM\ORMInterface;
use InvalidArgumentException;

final class FixtureLoadCommandHandler
{
    public function __construct(
        private FixtureConfig $config,
        private ORMInterface $orm,
    ) {}

    public function __invoke()
    {
        /** @var class-string<FixtureInterface> $fixtureClass */
        foreach ($this->config->fixtures as $fixtureClass) {

            if (!class_exists($fixtureClass)) {
                throw new InvalidArgumentException("Fixture class $fixtureClass does not exist");
            }

            /** @var FixtureInterface $fixture */
            $fixture = new $fixtureClass();
            if (false === $fixture instanceof FixtureInterface) {
                throw new InvalidArgumentException('Fixture class must implement ' . FixtureInterface::class);
            }

            $fixture->load($this->orm);
        }
    }
}

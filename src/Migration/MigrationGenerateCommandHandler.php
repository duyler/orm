<?php

declare(strict_types=1);

namespace Duyler\ORM\Migration;

use Cycle\Migrations\Migration;
use DateTime;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Override;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class MigrationGenerateCommandHandler
{
    public function __construct(
        private MigrationConfig $config,
    ) {}

    public function __invoke()
    {
        $date = new DateTime();
        $dateFilePrefix = $date->format('Ymd.His');

        $answer = (string) readline('Enter a migration name: ');

        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
        $postfixName = $answer === '' ? 'default' : $nameConverter->normalize($answer);

        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace('Migrations');
        $namespace->addUse(Migration::class);
        $namespace->addUse(Override::class);

        $class = $namespace->addClass('Migration_' . $date->format('YmdHis') . '_' . $postfixName);
        $class
            ->setFinal()
            ->setExtends(Migration::class)
            ->addConstant('DATABASE', 'default')
            ->setProtected()
            ->setType('string');

        $upMethod = $class->addMethod('up');
        $upMethod
            ->setReturnType('void')
            ->addAttribute('Override');

        $downMethod = $class->addMethod('down');
        $downMethod
            ->setReturnType('void')
            ->addAttribute('Override');

        $fileContent = (new PsrPrinter())->printFile($file);

        file_put_contents(
            $this->config->directory . '/' . $dateFilePrefix . '_' . $postfixName . '.php',
            $fileContent,
        );
    }
}

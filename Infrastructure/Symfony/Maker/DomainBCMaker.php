<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Maker;


use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Makes a boilerplate for a cqrs bounded context
 * Class DomainBCMaker
 * @package Xefiji\Seasons\Infrastructure\Symfony\Maker
 */
final class DomainBCMaker extends AbstractMaker
{
    private $kernelRootDir;
    private $name;
    private $dir;
    private $namespace;

    /**
     * DomainBCMaker constructor.
     * @param $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }


    /**
     * Return the command name for your maker (e.g. make:report).
     *
     * @return string
     */
    public static function getCommandName(): string
    {
        return "domain:make:bc";
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Command $command
     * @todo review Args and namespace building. Should be more intuitive
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->name = $io->ask("Bounded context name ?");
        $this->dir = $io->ask("Directory to save classes in ?", "Domain");
        $this->namespace = $io->ask("Bounded context namespace ?", "Domain");

        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name;
        if (false === $io->confirm("You are about to create all files in {$dir}. Are you sure ?")) {
            $io->error("Exiting");
            exit;
        }
    }

    /**
     * Configure the command: set description, input arguments, options, etc.
     *
     * By default, all arguments will be asked interactively. If you want
     * to avoid that, use the $inputConfig->setArgumentAsNonInteractive() method.
     *
     * @param Command $command
     * @param InputConfiguration $inputConfig
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command->setDescription('Creates a new Bounded Context directory with boilerplate classes');
    }

    /**
     * Configure any library dependencies that your maker requires.
     *
     * @param DependencyBuilder $dependencies
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // TODO: Implement configureDependencies() method.
    }

    /**
     * Called after normal code generation: allows you to do anything.
     *
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $this->makeBaseClasses($input, $io, $generator);
        $this->makeCommandClasses($input, $io, $generator);
        $this->makeEventClasses($input, $io, $generator);
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function makeBaseClasses(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name;
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        //Aggregate
        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name
        );

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/Aggregate.tpl.php'
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        //AggregateId
        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name,
            'Id'
        );

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/AggregateId.tpl.php'
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        //Repo
        $subscriberClassNameDetails = $generator->createClassNameDetails(
            "EventStore" . $this->name,
            $this->namespace . "\\" . $this->name,
            'Repository'
        );

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/EventStoreAggregateRepository.tpl.php', [
                'aggregate_class_name' => $this->name
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function makeCommandClasses(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . 'Command';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        //Command
        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name . "\\Command",
            "Command"
        );
        $commandShortName = $subscriberClassNameDetails->getShortName();
        $commandFullName = $subscriberClassNameDetails->getFullName();
        $aggregateNamespace = "App\\" . $this->namespace . "\\" . $this->name;

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/Command.tpl.php', [
                'aggregate_class_name' => $this->name
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        //CommandHandler
        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . 'Handler';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name . "\\Handler",
            "CommandHandler"
        );

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/CommandHandler.tpl.php', [
                'aggregate_class_name' => $this->name,
                'command_class_name' => $commandShortName,
                'command_full_name' => $commandFullName,
                'aggregateNamespace' => $aggregateNamespace
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }

    /**
     * @param InputInterface $input
     * @param ConsoleStyle $io
     * @param Generator $generator
     */
    private function makeEventClasses(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . 'Event';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        //Command
        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name . "\\Event",
            "Event"
        );
        $eventShortName = $subscriberClassNameDetails->getShortName();
        $eventFullName = $subscriberClassNameDetails->getFullName();
        $aggregateNamespace = "App\\" . $this->namespace . "\\" . $this->name;

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/Event.tpl.php', [
                'aggregate_class_name' => $this->name
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        //Event serialization:
        $datas = [
            $eventFullName => [
                "exclusion_policy" => "NONE",
                "properties" => [
                    strtolower($this->name) . "Id" => [
                        "type" => "string"
                    ],
                    "someThing" => [
                        "type" => "string"
                    ],
                    "someThingElse" => [
                        "type" => "string"
                    ],
                    "by" => [
                        "type" => "string"
                    ],
                    "createdAt" => [
                        "type" => "DateTimeImmutable"
                    ],
                    "aggregateId" => [
                        "type" => "string"
                    ],
                    "version" => [
                        "type" => "int"
                    ],
                ]
            ]
        ];
        $fileName = str_replace("\\", ".", $eventFullName) . ".yml";
        file_put_contents($dir . DIRECTORY_SEPARATOR . $fileName, Yaml::dump($datas, 4));

        //EventHandler
        $dir = $this->kernelRootDir . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . 'Handler';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $subscriberClassNameDetails = $generator->createClassNameDetails(
            $this->name,
            $this->namespace . "\\" . $this->name . "\\Handler",
            "EventHandler"
        );

        $generator->generateClass(
            $subscriberClassNameDetails->getFullName(),
            __DIR__ . DIRECTORY_SEPARATOR . 'Resources/skeleton/EventHandler.tpl.php', [
                'aggregate_class_name' => $this->name,
                'event_class_name' => $eventShortName,
                'event_full_name' => $eventFullName,
                'aggregateNamespace' => $aggregateNamespace
            ]
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);
    }

}
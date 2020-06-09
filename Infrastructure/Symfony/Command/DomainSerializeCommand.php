<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Command;

use Xefiji\Seasons\Event\IDomainEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DomainSerializeCommand
 * @package Xefiji\Seasons\Infrastructure\Symfony\Command
 */
class DomainSerializeCommand extends Command
{
    const NAMESPACE_SEPARATOR = "\\";

    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var string
     */
    private $destDir;

    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $interact;

    /**
     * @var array
     */
    private $knownTypes;


    protected static $defaultName = 'domain:serialize';

    /**
     * DomainSerializeCommand constructor.
     * @param null|string $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Build serialization files for events or commands, based on phpdoc')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Base folders to look for', "Domain")
            ->addOption('extension', null, InputOption::VALUE_REQUIRED, 'File extension', "*.php")
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Base namespace', "App\\Domain\\")
            ->addOption('dest_dir', null, InputOption::VALUE_REQUIRED, 'Destination dir for all event or command files, in namespace', "Infrastructure\\Serializer")
            ->addOption('class', null, InputOption::VALUE_REQUIRED, 'Specific event or command class to process', null);
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->destDir = $input->getOption('dest_dir');
        $this->path = $this->kernelRootDir . DIRECTORY_SEPARATOR . $input->getOption("path");
        $this->interact = !($input->getOption('no-interaction'));

        //@todo do it less brute force but with regex
        $this->knownTypes = [
            "\\DateTimeImmutable" => "DateTimeImmutable",
            "?\\DateTimeImmutable" => "DateTimeImmutable",
            "\\DateTime" => "DateTime",
            "?\\DateTime" => "DateTime",
            "int" => "int",
            "?int" => "int",
            "integer" => "integer",
            "?integer" => "integer",
            "string" => "string",
            "?string" => "string",
            "string[]" => "array<string>",
            "?string[]" => "array<string>",
            "float" => "float",
            "?float" => "float",
            "array" => "array",
            "?array" => "array",
            "bool" => "bool",
            "boolean" => "boolean",
        ];

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title("Processing all class files in {$this->path}");

        if ($class = $input->getOption('class')) {
            $this->buildSerializationFile(new \ReflectionClass($class));
        } else {
            $this->findAndBuildSerializationFile($input);
        }
    }

    /**
     * @param InputInterface $input
     */
    private function findAndBuildSerializationFile(InputInterface $input)
    {
        $finder = new Finder();
        $filtered = $finder->files()
            ->in($this->path)
            ->ignoreDotFiles(true)
            ->name($input->getOption("extension"));

        /**@var \Symfony\Component\Finder\SplFileInfo[] $filtered * */
        foreach ($filtered as $file) {
            try {
                $class = str_replace("." . $file->getExtension(), "", $file->getRelativePathname());
                $class = str_replace(DIRECTORY_SEPARATOR, self::NAMESPACE_SEPARATOR, $class);
                $fullClassName = $input->getOption('namespace') . $class;
                $r = new \ReflectionClass($fullClassName);
                if ($r->implementsInterface(IDomainEvent::class)) {
                    $this->buildSerializationFile($r);
                }

                if ($r->implementsInterface(\Xefiji\Seasons\Command\Command::class)) {
                    $this->buildSerializationFile($r);
                }
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            }

        }
    }

    /**
     * @param \ReflectionClass $r
     */
    private function buildSerializationFile(\ReflectionClass $r)
    {
        $properties = [];
        $this->io->section("Processing " . $r->getName());

        foreach ($r->getProperties() as $property) {
            $type = "string";
            if ($doc = $property->getDocComment()) {
                if (preg_match('#@var{1}\s+(\D+)\s{1}\*{1}#', $doc, $matches)) {
                    if (isset($matches[1])) {
                        $trimmed = trim($matches[1]);
                        if (array_key_exists($trimmed, $this->knownTypes)) {
                            $type = $this->knownTypes[$trimmed];
                        }
                    }
                }

                //$properties[$conv->normalize($property->getName())] = [ //not working on deserialize side ?
                $properties[$property->getName()] = ["type" => $type];
            }
        }

        $classDatas = [$r->getName() => ["exclusion_policy" => "NONE", "properties" => $properties]];

        if (count($r->getProperties()) != count($properties)) {
            $this->io->error(sprintf("Inconsistency detected: %d properties in class and %d were serialized. Are you sure that phpdoc is well formed for all attributes ?", count($r->getProperties()), count($properties)));
        } else {

            $this->displayRecap($classDatas[$r->getName()]['properties']);

            if ($this->interact) {
                if ($this->io->confirm("OK to generate serialization file ?", false)) {
                    $this->save($classDatas);
                } else {
                    $this->io->warning("Skipped");
                }
            } else {
                $this->save($classDatas);
            }
        }

        if ($this->interact) {
            sleep(1); //for output readability
        }
    }

    /**
     * @param $classDatas
     */
    private function save($classDatas)
    {
        $finalPath = $this->path . DIRECTORY_SEPARATOR . (str_replace("\\", "/", $this->destDir)) . DIRECTORY_SEPARATOR . str_replace("\\", ".", array_keys($classDatas)[0]) . ".yml";
        file_put_contents($finalPath, Yaml::dump($classDatas, 4));
        $this->io->success(sprintf("Successfully serialized in %s", $finalPath));
    }

    /**
     * @param $properties
     */
    private function displayRecap($properties)
    {
        $headers = ["Property", "Type"];
        $rows = [];
        foreach ($properties as $property => $value) {
            $rows[] = [$property, $value['type']];
        }

        $this->io->table($headers, $rows);

    }
}

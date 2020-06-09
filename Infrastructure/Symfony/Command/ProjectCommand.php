<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Command;


use Xefiji\Seasons\Aggregate\GenericId;
use Xefiji\Seasons\Infrastructure\Doctrine\DoctrineEventStore;
use Xefiji\Seasons\Benchmark;
use Xefiji\Seasons\Projection\DefaultProjector;
use Xefiji\Seasons\Projection\ProjectionCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProjectCommand
 * @package Xefiji\Seasons\Infrastructure\Symfony\Command
 */
class ProjectCommand extends Command implements ProjectionCommand
{
    /** @var string */
    protected static $defaultName = 'domain:project';

    /** @var OutputInterface */
    private $output;

    /** @var InputInterface */
    private $input;

    /** @var DefaultProjector */
    private $projector;

    /** @var DoctrineEventStore */
    private $eventStore;

    private $sinceId;
    private $sinceDate;
    private $projection;
    private $aggregate;
    private $event;
    private $reset = false;

    public function __construct(DefaultProjector $projector, DoctrineEventStore $eventStore)
    {
        $this->projector = $projector;
        $this->eventStore = $eventStore;
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription('Project denormalized datas')
            ->addOption('projection', null, InputOption::VALUE_OPTIONAL, 'Specific projection to be executed', null)
            ->addOption('aggregate', null, InputOption::VALUE_OPTIONAL, 'Specific aggregate to be projected', null)
            ->addOption('event', null, InputOption::VALUE_OPTIONAL, 'Specific event to be projected', null)
            ->addOption('sinceDate', null, InputOption::VALUE_OPTIONAL, 'Project since a specific date', null)
            ->addOption('sinceId', null, InputOption::VALUE_OPTIONAL, 'Project since a specific event id', null)
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Will reset all projections, or specified one if given');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;
        $this->input = $input;

        if ($aggregate = $input->getOption('aggregate')) {
            $this->aggregate = $aggregate;
        }

        if ($projection = $input->getOption('projection')) {
            $this->projection = $projection;
        }

        if ($event = $input->getOption('event')) {
            $this->event = $event;
        }

        if ($sinceDate = $input->getOption('sinceDate')) {
            $this->sinceDate = new \DateTimeImmutable($sinceDate);
        }

        if ($sinceId = $input->getOption('sinceId')) {
            $this->sinceId = (int)$sinceId;
        }

        if ($reset = $input->getOption('reset')) {
            $this->reset = $reset;
        }

        if ($this->reset) {
            $this->reset($this->projection);
        }

        $this->project();
    }

    /**
     * @param null $projection
     */
    public function reset($projection = null): void
    {
        try {
            if ($projection) {
                $this->output->writeln("<comment>Reset {$projection}</comment>");
                $this->projector->resetProjections($projection, $this->aggregate);
            } else {
                $nb = count($this->projector->getProjections());
                $this->output->writeln("<comment>Reset {$nb} projections</comment>");
                $this->projector->resetProjections(null, $this->aggregate);
            }
        } catch (\Exception $e) {
            $this->output->writeln("<cerror>" . $e->getMessage() . "</cerror>");
        }
        Benchmark::instance()->start();

        $this->end(false);
    }

    public function project(): void
    {
        Benchmark::instance()->start();

        /**@var \Doctrine\ORM\Internal\Hydration\IterableResult $iterator * */
        list($count, $iterator) = $this->getQueryElements();

        $this->output->writeln("<comment>{$count} events to process</comment>");
        if ((int)$count === 0) {
            return;
        }

        $progressBar = new ProgressBar($this->output, (int)$count);
        $progressBar->start();

        while ($iterator->next()) {
            try {
                $event = $iterator->current()[0];
                $this->projector->project($event);
            } catch (\Exception $e) {
                $this->output->writeln("<error>" . $e->getMessage() . "</error>");
            } finally {
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->end();
    }

    /**
     * Switches between parameters passed as option, filters on desired elements and returns.
     *  - total elements
     *  - iterator for that query
     * Order matters.
     * @return array
     */
    private function getQueryElements(): array
    {
        if ($projection = $this->projection) {
            if (!class_exists($projection)) {
                $this->output->writeln("<error>Class {$projection} does not exist</error>");
                $this->end(true);
            }
            $this->projector->filter($projection);
            $filteredEvents = forward_static_call([$this->projection, 'subscribedEvents']);
        }

        //specific projection is asked. we will filter on it's own events only to avoid querying the whole ES.
        if ($projection) {

            $aggregateId = $this->aggregate ? GenericId::fromString($this->aggregate) : null;
            $filteredEvents = isset($filteredEvents) ? $filteredEvents : [];
            $sinceId = $this->sinceId ? $this->sinceId : null;
            $sinceDate = $this->sinceDate ? $this->sinceDate : null;

            //specific event is asked, just filter it
            if ($this->event) {
                $res = [];
                foreach ($filteredEvents as $filteredEvent) {
                    $r = new \ReflectionClass($filteredEvent);
                    if ($r->getShortName() !== $this->event) {
                        continue;
                    }
                    $res[] = $filteredEvent;
                }
                $filteredEvents = $res;
            }

            $count = $this->eventStore->count($aggregateId, $filteredEvents, $sinceId, $sinceDate);
            $iterator = $this->eventStore->queryIterator($aggregateId, $filteredEvents, $sinceId, $sinceDate);

            return [$count, $iterator];
        }

        //specific aggregate is asked. Fetch only its related events
        if ($this->aggregate !== null) {
            $count = $this->eventStore->count(GenericId::fromString($this->aggregate));
            $iterator = $this->eventStore->iterateFor(GenericId::fromString($this->aggregate)); // @todo use \Domain\Infrastructure\Persistence\DoctrineEventStore::queryIterator

            return [$count, $iterator];
        }

        //fetch only those events that have an id > to sinceId
        if ($this->sinceId !== null) {
            $count = $this->eventStore->count(null, [], $this->sinceId);
            $iterator = $this->eventStore->iterateSince($this->sinceId); // @todo use \Domain\Infrastructure\Persistence\DoctrineEventStore::queryIterator

            return [$count, $iterator];
        }

        //fetch only those events that happened after sinceDate
        if ($this->sinceDate !== null) {
            $count = $this->eventStore->count(null, [], null, $this->sinceDate);
            $iterator = $this->eventStore->iterateSinceDate($this->sinceDate); // @todo use \Domain\Infrastructure\Persistence\DoctrineEventStore::queryIterator

            return [$count, $iterator];
        }

        //Normal flow: get all events
        $count = $this->eventStore->count(null, [], 0);
        $iterator = $this->eventStore->iterateSince(0); // @todo use \Domain\Infrastructure\Persistence\DoctrineEventStore::queryIterator

        return [$count, $iterator];
    }

    private function end($exit = false)
    {
        Benchmark::instance()->end();
        $this->output->writeln("");
        $this->output->writeln("<comment>Execution time " . Benchmark::instance()->toMinutes() . " minutes</comment>");
        if ($exit) {
            exit;
        }
    }
}
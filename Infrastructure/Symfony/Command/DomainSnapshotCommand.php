<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Command;


use Xefiji\Seasons\Aggregate\AggregateRepository;
use Xefiji\Seasons\Aggregate\GenericId;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventStore;
use Xefiji\Seasons\Helper\Date;
use Xefiji\Seasons\Helper\Time;
use Xefiji\Seasons\Infrastructure\Doctrine\DoctrineSnapshotRepository;
use Xefiji\Seasons\Infrastructure\Uuid;
use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Messaging\NotificationCommand;
use Xefiji\Seasons\Messaging\NotificationService;
use Xefiji\Seasons\Snapshot\Snapshot;
use Xefiji\Seasons\Snapshot\SnapshotRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DomainSnapshotCommand
 * @package Xefiji\Seasons\Infrastructure\Symfony\Command
 */
class DomainSnapshotCommand extends Command
{
    use CommandWorkerCapability;

    /** @var string */
    protected static $defaultName = 'domain:snapshot';

    /**
     * @var string
     */
    private $aggregate;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var DoctrineSnapshotRepository
     */
    private $snapshotRepository;

    /**
     * DomainSnapshotCommand constructor.
     * @param EventStore $eventStore
     * @param SnapshotRepository $snapshotRepository
     */
    public function __construct(EventStore $eventStore, SnapshotRepository $snapshotRepository)
    {
        parent::__construct();
        $this->eventStore = $eventStore;
        $this->snapshotRepository = $snapshotRepository;
    }


    public function configure()
    {
        $this
            ->setDescription('Spreads events')
            ->addOption('aggregateId', null, InputOption::VALUE_OPTIONAL, 'Specifiy an aggregate id to handle snapshots for', null)
            ->addOption('snapVersion', null, InputOption::VALUE_OPTIONAL, 'Remove a specific snapshot version', null)
            ->addOption('aggregateClass', null, InputOption::VALUE_OPTIONAL, 'Specifiy an aggregate class to handle snapshots for', null)
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Will reset all snapshot, or specified one if given')
            ->addOption('generate', null, InputOption::VALUE_NONE, 'Will generate snapshots for given aggregate, by iterating over its events')
            ->addOption('list', null, InputOption::VALUE_NONE, 'Will list all snapshot or aggregate\'s one if specified');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initialize($input, $output)
            ->start();

        $aggregateClass = $input->getOption('aggregateClass');
        $aggregateId = $input->getOption('aggregateId');
        $aggregateIdObj = $aggregateId ? GenericId::fromString($aggregateId) : null;

        if ($input->getOption('list')) {

            $this->io->comment("Listing current snapshots");

            $snapshots = $this->snapshotRepository->findAll($aggregateIdObj, $aggregateClass);
            if (!count($snapshots)) {
                $this->io->warning("No snapshot recorded");
            } else {
                $rows = [];
                /**@var Snapshot $snapshot */
                foreach ($snapshots as $snapshot) {
                    $rows[] = [
                        $snapshot->getId(),
                        $snapshot->getAggregateId(),
                        $snapshot->getAggregateClass(),
                        $snapshot->getVersion(),
                        $snapshot->getCreatedAt()->format(Date::FORMAT_US . " " . Time::FORMAT_W_SEC),
                    ];
                }

                $table = new Table($output);
                $table->setHeaders(["snapshotId", "aggregateId", "aggregateClass", "version", "createdAt"])
                    ->setRows($rows)->render();
            }
        }

        if ($reset = $input->getOption('reset')) {
            $this->io->comment("Resetting snapshots");
            $this->snapshotRepository->removeFor($aggregateIdObj, $aggregateClass, $input->getOption('snapVersion'));
        }

        if ($input->getOption('generate') && $aggregateIdObj && $aggregateClass) {
            $this->io->comment(sprintf("Generating snapshots for %s #%s", $aggregateClass, (string)$aggregateIdObj->value()));
            $aggregate = forward_static_call([$aggregateClass, 'reconstitute'], AggregateRepository::loadEvents($aggregateIdObj, $this->eventStore));
            $aggregate->snapshot(true);
        }

    }
}
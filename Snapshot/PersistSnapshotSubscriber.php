<?php

namespace Xefiji\Seasons\Snapshot;

use Xefiji\Seasons\Aggregate\AggregateInterface;

/**
 * Class PersistSnapshotSubscriber
 * @package Xefiji\Seasons\Snapshot
 */
final class PersistSnapshotSubscriber implements SnapshotSubscriber
{
    /**
     * @var SnapshotRepository
     */
    private $snapshotRepository;

    public function __construct(SnapshotRepository $snapshotRepository)
    {
        $this->snapshotRepository = $snapshotRepository;
    }

    /**
     * @param AggregateInterface $aggregate
     * @return mixed
     */
    public function handle(AggregateInterface $aggregate)
    {
        $this->snapshotRepository->save(new Snapshot($aggregate, $aggregate->playhead()));
    }

    /**
     * @param AggregateInterface $aggregate
     * @return mixed
     */
    public function isSubscribed(AggregateInterface $aggregate)
    {
        return true;
    }
}
<?php

namespace Xefiji\Seasons\Snapshot;
use Xefiji\Seasons\Aggregate\AggregateInterface;

/**
 * Interface SnapshotSubscriber
 * @package Xefiji\Seasons\Event
 */
interface SnapshotSubscriber
{
    /**
     * @param AggregateInterface $aggregate
     * @return mixed
     */
    public function handle(AggregateInterface $aggregate);

    /**
     * @param AggregateInterface $aggregate
     * @return mixed
     */
    public function isSubscribed(AggregateInterface $aggregate);
}
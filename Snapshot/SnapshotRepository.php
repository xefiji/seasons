<?php

namespace Xefiji\Seasons\Snapshot;


use Xefiji\Seasons\Aggregate\AggregateId;

/**
 * Interface SnapshotRepository
 * @package Xefiji\Seasons\Snapshot
 */
interface SnapshotRepository
{
    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return mixed
     */
    public function find(AggregateId $aggregateId, string $aggregateClass = null): ?Snapshot;

    /**
     * @param AggregateId|null $aggregateId
     * @param string|null $aggregateClass
     * @return array
     */
    public function findAll(AggregateId $aggregateId = null, string $aggregateClass = null): array;

    /**
     * @param Snapshot $snapshot
     * @return mixed
     */
    public function save(Snapshot $snapshot): void;

    /**
     * @param AggregateId|null $aggregateId
     * @param string|null $aggregateClass
     * @param null $version
     */
    public function removeFor(AggregateId $aggregateId = null, string $aggregateClass = null, $version = null): void;
}
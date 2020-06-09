<?php

namespace Xefiji\Seasons\Aggregate;


use Xefiji\Seasons\Event\EventStore;
use Xefiji\Seasons\Event\EventStream;
use Xefiji\Seasons\Exception\AggregateNotFoundException;
use Xefiji\Seasons\Serializer\DomainSerializer;
use Xefiji\Seasons\Snapshot\Snapshot;
use Xefiji\Seasons\Snapshot\SnapshotRepository;

/**
 * Class AggregateRepository
 * @package Xefiji\Seasons\Aggregate
 * @todo shouldn't it be an EventSourcedRepository ?
 */
abstract class AggregateRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var SnapshotRepository
     */
    private $snapshotRepository;

    /**
     * AggregateRepository constructor.
     * @param EventStore|null $eventStore
     * @param SnapshotRepository|null $snapshotRepository
     */
    public function __construct(EventStore $eventStore = null, SnapshotRepository $snapshotRepository = null)
    {
        $this->eventStore = $eventStore;
        $this->snapshotRepository = $snapshotRepository;
    }

    /**
     * @param Aggregate $aggregate
     */
    protected function store(Aggregate $aggregate): void
    {
        $this->eventStore->appendAll(new EventStream($aggregate->id(), $aggregate->recorded()));
    }

    /**
     * @param $id
     * @param null $aggregateClass
     * @return mixed
     * @throws AggregateNotFoundException
     */
    protected function load($id, $aggregateClass = null): EventStream
    {
        $eventStream = $this->eventStore->getEventsFor($id, $aggregateClass);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

    /**
     * Loads all events for aggregate, until given event Id
     * @param $id
     * @param $eventId
     * @param null $aggregateClass
     * @return EventStream
     * @throws AggregateNotFoundException
     */
    protected function loadUntil($id, $eventId, $aggregateClass = null): EventStream
    {
        $eventStream = $this->eventStore->getEventsForUntil($id, (int)$eventId, $aggregateClass);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

    /**
     * Loads all events for aggregate, since given playhead.
     * Mainly for snapshot purposes
     * @param $id
     * @param $playhead
     * @param null $aggregateClass
     * @return EventStream
     */
    protected function loadSincePlayhead($id, $playhead, $aggregateClass = null): EventStream
    {
        return $this->eventStore->getEventsSincePlayhead($id, (int)$playhead, $aggregateClass);
    }

    /**
     * Same as load, but static (pass event store as arg)
     * @param AggregateId $id
     * @param EventStore $eventStore
     * @return EventStream
     * @throws AggregateNotFoundException
     */
    public static function loadEvents(AggregateId $id, EventStore $eventStore): EventStream
    {
        $eventStream = $eventStore->getEventsFor($id);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

    /**
     * Same as load, but from snapshot
     * @param $id
     * @param null $aggregateClass
     * @return null|AggregateInterface
     */
    public function loadFromSnapShot($id, $aggregateClass = null): ?AggregateInterface
    {
        if ($snapshot = $this->snapshotRepository->find($id, $aggregateClass)) {
            /**@var \Xefiji\Seasons\Aggregate\Aggregate $aggregate * */
            $aggregate = DomainSerializer::instance()->defaultDeserialise($snapshot->getAggregate());
            $aggregate->setSnapshotVersion($snapshot->getVersion());
            return $aggregate;
        }
        return null;
    }

    /**
     * @param $id
     * @param null $aggregateClass
     * @return bool
     */
    public function exists($id, $aggregateClass = null): bool
    {
        return $this->eventStore->has($id, $aggregateClass);
    }

    /**
     * @return bool
     */
    public function hasSnapshotRepositorySetted(): bool
    {
        return !is_null($this->snapshotRepository);
    }

    /**
     * @return bool
     */
    public function hasEventStoreSetted(): bool
    {
        return !is_null($this->eventStore);
    }

}
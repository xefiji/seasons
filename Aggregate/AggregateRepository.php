<?php

namespace Xefiji\Seasons\Aggregate;


use Xefiji\Seasons\Event\EventStore;
use Xefiji\Seasons\Event\EventStream;
use Xefiji\Seasons\Exception\AggregateNotFoundException;

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


    public function __construct(EventStore $eventStore = null)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param Aggregate $aggregate
     */
    protected function store(Aggregate $aggregate)
    {
        $this->eventStore->appendAll(new EventStream($aggregate->id(), $aggregate->recorded()));
    }

    /**
     * @param $id
     * @param null $aggregateClass
     * @return mixed
     * @throws AggregateNotFoundException
     */
    protected function load($id, $aggregateClass = null)
    {
        $eventStream = $this->eventStore->getEventsFor($id, $aggregateClass);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

    protected function loadUntil($id, $eventId, $aggregateClass = null)
    {
        $eventStream = $this->eventStore->getEventsForUntil($id, (int)$eventId, $aggregateClass);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

    /**
     * @param AggregateId $id
     * @param EventStore $eventStore
     * @return EventStream
     * @throws AggregateNotFoundException
     */
    public static function loadEvents(AggregateId $id, EventStore $eventStore)
    {
        $eventStream = $eventStore->getEventsFor($id);
        if ($eventStream->count() === 0) {
            throw new AggregateNotFoundException($id);
        }

        return $eventStream;
    }

}
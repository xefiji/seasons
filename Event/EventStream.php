<?php

namespace Xefiji\Seasons\Event;

use Xefiji\Seasons\Aggregate\AggregateId;


/**
 * Class EventStream
 * @package Xefiji\Seasons
 */
final class EventStream implements \IteratorAggregate
{
    /**
     * @var AggregateId
     */
    private $aggregateId;

    /**
     * @var array|\mixed[]
     */
    private $events;

    /**
     * @param AggregateId $aggregateId
     * @param mixed[] $events
     */
    public function __construct(AggregateId $aggregateId = null, array $events)
    {
        $this->aggregateId = $aggregateId;
        $this->events = $events;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * @return AggregateId
     */
    public function aggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return AggregateId
     */
    public function count()
    {
        return count($this->events);
    }
}
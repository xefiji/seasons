<?php

namespace Xefiji\Seasons\tests;


use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventStore;
use Xefiji\Seasons\Event\EventStream;

class MockEventStore implements EventStore
{
    /**
     * @var DomainEvent[]
     */
    private $domainEvents = [];

    /**
     * Just for mocks and dependecies between tests functions
     * @param $events
     */
    public function setEvents($events)
    {
        $this->domainEvents = $events;
    }

    /**
     * Just for mocks and dependecies between tests functions
     * @return \Xefiji\Seasons\Event\DomainEvent[]
     */
    public function getEvents()
    {
        return $this->domainEvents;
    }

    /**
     * @param EventStream $eventStream
     * @return mixed
     */
    public function appendAll(EventStream $eventStream)
    {
        foreach ($eventStream as $domainEvent) {
            $this->domainEvents[] = $domainEvent;
        }
    }

    /**
     * @param DomainEvent $domainEvent
     * @return mixed
     */
    public function append(DomainEvent $domainEvent)
    {
        $this->domainEvents[] = $domainEvent;
    }

    /**
     * @param AggregateId $aggregateId
     * @param $aggregateClass
     * @return EventStream
     */
    public function getEventsFor(AggregateId $aggregateId, string $aggregateClass = null): EventStream
    {
        $res = [];
        foreach ($this->domainEvents as $domainEvent) {
            if ((string)$domainEvent->getAggregateId() === (string)$aggregateId->value()) {
                $res[] = $domainEvent;
            }
        }

        return new EventStream($aggregateId, $res);
    }

    /**
     * @param $eventId
     * @return EventStream
     */
    public function allEventsSince($eventId)
    {
        $res = [];
        foreach ($this->domainEvents as $index => $domainEvent) {
            if ($index >= $eventId) {
                $res[] = $domainEvent;
            }
        }

        return new EventStream(null, $res);
    }

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return mixed
     */
    public function allEventsSinceDate(\DateTimeImmutable $dateTimeImmutable)
    {
        // TODO: Implement allEventsSinceDate() method.
    }

    /**
     * @return mixed
     */
    public function lastEvent()
    {
        // TODO: Implement lastEvent() method.
    }

    /**
     * @param $eventId
     * @return mixed
     */
    public function iterateSince($eventId)
    {
        // TODO: Implement iterateSince() method.
    }

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return mixed
     */
    public function iterateSinceDate(\DateTimeImmutable $dateTimeImmutable)
    {
        // TODO: Implement iterateSinceDate() method.
    }

    /**
     * @param AggregateId $aggregateId
     * @param $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return mixed
     */
    public function count(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null)
    {
        // TODO: Implement count() method.
    }

    /**
     * @param AggregateId|null $aggregateId
     * @param $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return mixed
     */
    public function queryIterator(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null)
    {
        // TODO: Implement queryIterator() method.
    }

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return bool
     */
    public function has(AggregateId $aggregateId, string $aggregateClass = null): bool
    {
        // TODO: Implement has() method.
    }


    /**
     * @param AggregateId $aggregateId
     * @param $eventId
     * @param string|null $aggregateClass
     * @return mixed
     */
    public function getEventsForUntil(AggregateId $aggregateId, int $eventId, string $aggregateClass = null): EventStream
    {
        // TODO: Implement getEventsForUntil() method.
    }

    /**
     * @param AggregateId $aggregateId
     * @param $playhead
     * @param string|null $aggregateClass
     * @return EventStream
     */
    public function getEventsSincePlayhead(AggregateId $aggregateId, int $playhead, string $aggregateClass = null): EventStream
    {
        // TODO: Implement getEventsSincePlayhead() method.
    }

    /**
     * @param AggregateId $aggregateId
     * @param $aggregateClass
     * @return mixed
     */
    public function iterateFor(AggregateId $aggregateId, string $aggregateClass = null)
    {
        // TODO: Implement iterateFor() method.
    }
}
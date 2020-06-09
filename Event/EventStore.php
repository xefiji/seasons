<?php

namespace Xefiji\Seasons\Event;

use Xefiji\Seasons\Aggregate\AggregateId;


/**
 * Interface EventStore
 * @package Xefiji\Seasons
 * Requirements:
 * - Ability to start reading from any point in the stream
 * - Events are read in the order they occured
 * - The stream is append only
 */
interface EventStore
{
    /**
     * @param EventStream $eventStream
     * @return mixed
     */
    public function appendAll(EventStream $eventStream);

    /**
     * @param DomainEvent $domainEvent
     * @return mixed
     */
    public function append(DomainEvent $domainEvent);

    /**
     * @param AggregateId $aggregateId
     * @param string $aggregateClass
     * @return EventStream
     */
    public function getEventsFor(AggregateId $aggregateId, string $aggregateClass = null): EventStream;

    /**
     * @param AggregateId $aggregateId
     * @param int $eventId
     * @param string|null $aggregateClass
     * @return mixed
     */
    public function getEventsForUntil(AggregateId $aggregateId, int $eventId, string $aggregateClass = null): EventStream;

    /**
     * @param AggregateId $aggregateId
     * @param int $playhead
     * @param string|null $aggregateClass
     * @return EventStream
     */
    public function getEventsSincePlayhead(AggregateId $aggregateId, int $playhead, string $aggregateClass = null): EventStream;

    /**
     * @param AggregateId $aggregateId
     * @param string $aggregateClass
     * @return mixed
     */
    public function iterateFor(AggregateId $aggregateId, string $aggregateClass = null);

    /**
     * @param $eventId
     * @return EventStream
     */
    public function allEventsSince($eventId);

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return mixed
     */
    public function allEventsSinceDate(\DateTimeImmutable $dateTimeImmutable);

    /**
     * @return mixed
     */
    public function lastEvent();

    /**
     * @param $eventId
     * @return mixed
     */
    public function iterateSince($eventId);

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return mixed
     */
    public function iterateSinceDate(\DateTimeImmutable $dateTimeImmutable);

    /**
     * @param AggregateId $aggregateId
     * @param array $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return mixed
     */
    public function count(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null);

    /**
     * @param AggregateId|null $aggregateId
     * @param array $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return mixed
     */
    public function queryIterator(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null);

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return bool
     */
    public function has(AggregateId $aggregateId, string $aggregateClass = null): bool;
}
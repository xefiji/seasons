<?php

namespace Xefiji\Seasons\Aggregate;


use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventStream;

interface AggregateInterface
{
    public function id();

    public static function reconstitute(EventStream $history);

    public function recordApplyPublish(DomainEvent $event);

    public function record(DomainEvent $event);

    public function apply(DomainEvent $event);

    public function publish(DomainEvent $event);

    public function recorded();

    public function clearEvents();

    static function create(AggregateId $aggregateId = null);
}
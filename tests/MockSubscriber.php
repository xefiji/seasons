<?php

namespace Xefiji\Seasons\tests;

use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\DomainEventSubscriber;
use Xefiji\Seasons\Event\EventStore;

class MockSubscriber implements DomainEventSubscriber
{
    /**
     * @var DomainEvent
     */
    public $domainEvent;

    /**
     * @var EventStore
     */
    public $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param DomainEvent $domainEvent
     */
    public function handle(DomainEvent $domainEvent)
    {
        $this->domainEvent = $domainEvent;
        $this->eventStore->append($domainEvent);
    }

    /**
     * @param DomainEvent $domainEvent
     * @return bool
     */
    public function isSubscribed(DomainEvent $domainEvent)
    {
        return true;
    }
}
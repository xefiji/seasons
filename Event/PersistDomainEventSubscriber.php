<?php

namespace Xefiji\Seasons\Event;

/**
 * Class PersistDomainEventSubscriber
 * @package Xefiji\Seasons\Event
 *
 * Should be instanciated on Infra's side via e.g:
 *  DomainEventPublisher::instance()->subcribe(new PersistDomainEventSubscriber($eventStore))
 *
 */
final class PersistDomainEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param DomainEvent $domainEvent
     * @return void
     */
    public function handle(DomainEvent $domainEvent)
    {
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
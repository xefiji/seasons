<?php

namespace Xefiji\Seasons\Event;

/**
 * Interface DomainEventSubscriber
 * @package Xefiji\Seasons\Event
 */
interface DomainEventSubscriber
{
    /**
     * @param DomainEvent $domainEvent
     * @return void
     */
    public function handle(DomainEvent $domainEvent);

    /**
     * @param DomainEvent $domainEvent
     * @return bool
     */
    public function isSubscribed(DomainEvent $domainEvent);
}
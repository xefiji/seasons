<?php

namespace Xefiji\Seasons\Event;


/**
 * Class DomainEventPublisher
 * @package Xefiji\Seasons\Event
 */
class DomainEventPublisher
{
    /**
     * @var DomainEventSubscriber[]
     */
    private $subscribers;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * DomainEventPublisher constructor.
     */
    private function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * Singleton
     * @return DomainEventPublisher|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param DomainEventSubscriber $domainEventSubscriber
     * @return void
     */
    public function subcribe(DomainEventSubscriber $domainEventSubscriber)
    {
        $this->subscribers[] = $domainEventSubscriber;
    }

    /**
     * @param DomainEventSubscriber $domainEventSubscriber
     * @return void
     */
    public function subcribeOne(DomainEventSubscriber $domainEventSubscriber)
    {
        $this->subscribers = [$domainEventSubscriber];
    }

    /**
     * @param DomainEventSubscriber $domainEventSubscriber
     * @return void
     */
    public function unsubscribe(DomainEventSubscriber $domainEventSubscriber)
    {
        $subscribers = [];
        while ($subscriber = array_shift($this->subscribers)) {
            if ($subscriber != $domainEventSubscriber) {
                $subscribers[] = $subscriber;
            }
        }

        $this->subscribers = $subscribers;
    }

    /**
     * @param DomainEvent $domainEvent
     */
    public function publish(DomainEvent $domainEvent)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribed($domainEvent)) {
                $subscriber->handle($domainEvent);
            }
        }
    }


    public function __clone()
    {
        throw new \BadMethodCallException("Why would you clone a singleton ?");
    }
}
<?php

namespace Xefiji\Seasons\Bus;


use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventHandler;
use Xefiji\Seasons\Event\GroupEventHandler;

/**
 * Class BaseEventBus
 * @package Xefiji\Seasons\Bus
 */
class BaseEventBus implements EventBus
{
    /**
     * @var EventHandler[]
     */
    private $eventHandlers = [];

    /**
     * @var DomainEvent[]
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $isDispatching = false;


    /**
     * @param DomainEvent $event
     */
    public function dispatch(DomainEvent $event)
    {
        $this->queue[] = $event;

        if (!$this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($event = array_shift($this->queue)) {
                    foreach ($this->eventHandlers as $eventHandler) {

                        if ($eventHandler instanceof EventHandler) {
                            if ($eventHandler->listenTo() !== $event->getFullName()) {
                                continue;
                            }
                            $eventHandler->handle($event);
                        }

                        if ($eventHandler instanceof GroupEventHandler) {
                            if (!in_array($event->getFullName(), $eventHandler->listenTo())) {
                                continue;
                            }
                            $eventHandler->handle($event);
                        }
                    }
                }
            } finally {
                $this->isDispatching = false;
            }
        }
    }

    /**
     * @param EventHandler $handler
     * @return $this
     */
    public function subscribe(EventHandler $handler)
    {
        $this->eventHandlers[] = $handler;
        return $this;
    }

    /**
     * @param GroupEventHandler $handler
     * @return mixed
     */
    public function groupSubscribe(GroupEventHandler $handler)
    {
        $this->eventHandlers[] = $handler;
        return $this;
    }
}
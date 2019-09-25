<?php

namespace Xefiji\Seasons\Bus;


use Xefiji\Seasons\Event\EventHandler;
use Xefiji\Seasons\Event\DomainEvent;

/**
 * Interface EventBus
 * @package Xefiji\Seasons\Bus
 */
interface EventBus
{
    public function dispatch(DomainEvent $event);


    public function subscribe(EventHandler $handler);
}
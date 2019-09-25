<?php

namespace Xefiji\Seasons\Event;

/**
 * Interface GroupEventHandler
 * @package Xefiji\Seasons\Event
 */
interface GroupEventHandler
{
    const HANDLER_METHOD_PREFIX = "handle";

    /**
     * @return array
     */
    public function listenTo();

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function handle(DomainEvent $event);

    /**
     * @param DomainEvent $event
     * @return mixed
     */
    public function resolveHandlerMethod(DomainEvent $event);
}
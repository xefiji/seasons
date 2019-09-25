<?php

namespace Xefiji\Seasons\Projection;

use Xefiji\Seasons\Event\DomainEvent;

/**
 * Interface Projection
 * @package Xefiji\Seasons\Projection
 */
interface Projection
{
    /**
     * @return array
     */
    public function listenTo();

    /**
     * @param DomainEvent $event
     * @return mixed
     */
    public function project(DomainEvent $event);
}
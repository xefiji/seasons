<?php

namespace Xefiji\Seasons\Projection;

use Xefiji\Seasons\Event\DomainEvent;

/**
 * Interface Projector
 * @package Xefiji\Seasons\Projection
 */
interface Projector
{
    /**
     * @param Projection[] $projections
     */
    public function register($projections);

    /**
     * @param Projection[] $projections
     */
    public function unregister($projections);

    /**
     * @param DomainEvent[] $events
     */
    public function project($events);

    /**
     * @param Projection $projection
     */
    public function filter($projection);
}
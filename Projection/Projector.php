<?php

namespace Xefiji\Seasons\Projection;

use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\IDomainEvent;

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
     * @param $event
     */
    public function project($event): void;

    /**
     * @param Projection $projection
     */
    public function filter($projection);
}
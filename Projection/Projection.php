<?php

namespace Xefiji\Seasons\Projection;

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
     * @param $event
     * @return mixed|void
     */
    public function project($event): void;

    /**
     * @param null $aggregateId
     * @return void
     */
    public function reset($aggregateId = null): void;
}
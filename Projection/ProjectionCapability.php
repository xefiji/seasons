<?php

namespace Xefiji\Seasons\Projection;

/**
 * Class ProjectionCapability
 * @package Xefiji\Seasons\Projection
 */
trait ProjectionCapability
{
    /**
     * @return string
     * @todo make it automatic
     */
    public function getTrackerName():string
    {
        return self::PROJECTION_TRACKER_NAME; //should be declared as const string in the class using the trait
    }

    /**
     * @return array
     */
    public function listenTo(): array
    {
        return self::subscribedEvents();
    }

    /**
     * @return array
     */
    public static function subscribedEvents(): array
    {
        return self::$subscribedEvents; //should be declared as array in the class using the trait
    }


    /**
     * @param array $params
     * @return bool
     * @internal param $aggregateId
     */
    public function exists(...$params):bool
    {
        // TODO: Implement exists() method.
    }

    /**
     * @param array ...$params
     * @return mixed
     */
    public function getRow(...$params)
    {
        // TODO: Implement getRow() method.
    }

    /**
     * @param array ...$params
     * @return array
     */
    public function getRows(...$params):array
    {
        // TODO: Implement getRows() method.
    }
}
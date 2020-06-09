<?php

namespace Xefiji\Seasons\Aggregate;

/**
 * Class AggregateCapability
 * @package Xefiji\Seasons\Aggregate
 */
trait AggregateCapability
{
    /**
     * @var AggregateId
     */
    private $id;

    private function __construct()
    {
    }

    /**
     * @return AggregateId
     */
    public function id(): AggregateId
    {
        return $this->id;
    }

    /**
     * @param AggregateId|null $aggregateId
     * @return Aggregate
     */
    static function create(AggregateId $aggregateId = null)
    {
        $new = new self();
        $new->id = $aggregateId;
        return $new;
    }
}
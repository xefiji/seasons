<?php

namespace Xefiji\Seasons\Snapshot;


use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Aggregate\AggregateInterface;
use Xefiji\Seasons\Serializer\DomainSerializer;

/**
 * Class Snapshot
 * @package Xefiji\Seasons\Snapshot
 */
class Snapshot
{
    private $id; //whatever

    /**
     * @var AggregateId
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @var string
     */
    private $aggregate;

    /**
     * @var integer
     */
    private $version;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * Snapshot constructor.
     * @param AggregateInterface $aggregate
     * @param $version
     * @param bool $withEvents
     */
    public function __construct(AggregateInterface $aggregate, $version, $withEvents = false)
    {
        $this->aggregateId = $aggregate->id();
        $this->aggregateClass = get_class($aggregate);
        if (!$withEvents) {
            $aggregate->clearEvents();
        }
        //no way that we build serialization config for every aggregate. Just use php native one for snapshot purpose.
        $this->aggregate = DomainSerializer::instance()->defaultSerialise($aggregate);
        $this->version = $version;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return AggregateId
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function getAggregateClass(): string
    {
        return $this->aggregateClass;
    }

    /**
     * @return string
     */
    public function getAggregate(): string
    {
        return $this->aggregate;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

}
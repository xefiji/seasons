<?php

namespace Xefiji\Seasons\Aggregate;

/**
 * Class EntityAggregateMapping
 * @package Xefiji\Seasons\Aggregate
 */
class EntityAggregateMapping
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var AggregateId
     */
    private $aggregateId;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;


    public function __construct(AggregateId $aggregateId, $aggregateClass, $entityId, $entityClass)
    {
        $this->entityId = $entityId;
        $this->entityClass = $entityClass;
        $this->aggregateId = $aggregateId;
        $this->aggregateClass = $aggregateClass;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function id()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
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
    public function getAggregateClass()
    {
        return $this->aggregateClass;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

}
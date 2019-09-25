<?php

namespace Xefiji\Seasons\Event;

/**
 * Class DomainEventTrait
 * @package Xefiji\Seasons\Event
 */
trait DomainEventTrait
{
    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $by;

    /**
     * @var integer
     */
    protected $version = 0;

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $date
     */
    public function setCreatedAt(\DateTimeImmutable $date)
    {
        $this->createdAt = $date;
    }

    /**
     * @return string
     */
    public function by()
    {
        return $this->by;
    }

    /**
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function aggregateId()
    {
        return $this->aggregateId;
    }
}
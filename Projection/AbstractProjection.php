<?php


namespace Xefiji\Seasons\Projection;


use Xefiji\Seasons\Aggregate\EntityAggregateMapping;
use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\ChildEventException;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Messaging\PublishedMessageTracker;

/**
 * Class ProjectionResolver
 * @package Xefiji\Seasons\Projection
 */
abstract class AbstractProjection
{
    const METHOD_PREFIX = "on";
    const METHOD_SUFFIX = "event";

    /**
     * @var ReadModelRepository
     */
    protected $repository;

    /**
     * @return PublishedMessageTracker
     */
    protected $tracker;

    /**
     * @var int
     */
    protected $mostRecentTrackedId = 0;

    /**
     * @var array
     */
    static $subscribedEvents = [];

    /**
     * @return string
     */
    abstract public function getTrackerName():string;

    /**
     * @param $aggregateId
     * @return EntityAggregateMapping
     * @deprecated
     */
    abstract public function getMap($aggregateId):EntityAggregateMapping;

    /**
     * @param array $params
     * @return bool
     * @internal param $aggregateId
     */
    abstract public function exists(...$params):bool;

    /**
     * @param array ...$params
     * @return mixed
     */
    abstract public function getRow(...$params);

    /**
     * @param array ...$params
     * @return array
     */
    abstract public function getRows(...$params):array;

    /**
     * @param DomainEvent $event
     * @throws ChildEventException
     */
    public function project(DomainEvent $event):void
    {
        $method = self::METHOD_PREFIX . str_ireplace(self::METHOD_SUFFIX, "", $event->getEventType());
        $childEvent = $event->getPayload();
        if (!$childEvent) {
            throw new ChildEventException("ChildEvent {$event->getFullName()} could not be built in " . get_class($this));
        }

        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $childEvent);
            $this->track($event);
        } else {
            DomainLogger::instance()->warning(sprintf("method %s does not exist in %s", $method, get_class($this)));
        }
    }

    /**
     * @param PublishedMessageTracker $publishedMessageTracker
     */
    protected function initTracker(PublishedMessageTracker $publishedMessageTracker):void
    {
        if (null == ($trackerString = $this->getTrackerName())) {
            return;
        }

        $this->tracker = $publishedMessageTracker;
        $this->mostRecentTrackedId = $this->tracker->mostRecentPublishedMessageId($trackerString);
    }

    /**
     * @param DomainEvent $event
     */
    private function track(DomainEvent $event):void
    {
        if (null == ($trackerString = $this->getTrackerName())) {
            return;
        }

        if (!($this->tracker instanceof PublishedMessageTracker)) {
            throw new \LogicException(sprintf("Tracker %s must be of type %s", $trackerString, get_class($this->tracker)));
        }

        if ($this->mostRecentTrackedId < $event->getId()) {
            $this->tracker->trackMostRecentPublishedMessage($trackerString, $event->getId());
            $this->mostRecentTrackedId = $event->getId();
        }
    }
}
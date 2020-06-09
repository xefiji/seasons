<?php


namespace Xefiji\Seasons\Projection;


use Xefiji\Seasons\Aggregate\EntityAggregateMapping;
use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\ChildEventException;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\IDomainEvent;
use Xefiji\Seasons\Exception\DomainLogicException;
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
     * @param array $params
     * @return bool
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
     * @param $event
     * @throws ChildEventException
     */
    public function project($event): void
    {
        switch (true) {
            //@deprecated: or might be used only on bulk projections commands ?
            case $event instanceof DomainEvent:
                $method = self::METHOD_PREFIX . str_ireplace(self::METHOD_SUFFIX, "", $event->getEventType());
                $childEvent = $event->getPayload();
                if (!$childEvent) {
                    throw new ChildEventException("ChildEvent {$event->getFullName()} could not be built in " . get_class($this));
                }

                if (method_exists($this, $method)) {
                    call_user_func([$this, $method], $childEvent);
                    $this->track($event->getId());
                } else {
                    DomainLogger::instance()->warning(sprintf("method %s does not exist in %s", $method, get_class($this)));
                }
                break;

            case $event instanceof IDomainEvent:
                $method = self::METHOD_PREFIX . str_ireplace(self::METHOD_SUFFIX, "", DomainEvent::getClassName($event));
                if (method_exists($this, $method)) {
                    call_user_func([$this, $method], $event);
                    if (property_exists($event, IDomainEvent::ID)) {
                        $this->track($event->{IDomainEvent::ID});
                    }
                } else {
                    DomainLogger::instance()->warning(sprintf("method %s does not exist in %s", $method, get_class($this)));
                }
                break;
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
     * @param int $id
     * @throws DomainLogicException
     */
    private function track(int $id):void
    {
        if (null == ($trackerString = $this->getTrackerName())) {
            return;
        }

        if (!($this->tracker instanceof PublishedMessageTracker)) {
            throw new DomainLogicException(sprintf("Tracker %s must be of type %s", $trackerString, get_class($this->tracker)));
        }

        if ($this->mostRecentTrackedId < $id) {
            $this->tracker->trackMostRecentPublishedMessage($trackerString, $id);
            $this->mostRecentTrackedId = $id;
        }
    }

    /**
     * @param null $aggregateId
     * @return void
     */
    public function reset($aggregateId = null): void
    {
        //override
    }

}
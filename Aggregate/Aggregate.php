<?php

namespace Xefiji\Seasons\Aggregate;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\ChildEventException;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\DomainEventPublisher;
use Xefiji\Seasons\Event\EventStream;
use Xefiji\Seasons\Event\IDomainEvent;
use Xefiji\Seasons\Exception\MissingApplierMethodException;
use Xefiji\Seasons\Snapshot\DomainSnapshotPublisher;

/**
 * Class Aggregate (Root)
 * @package Xefiji\Seasons
 */
abstract class Aggregate implements AggregateInterface
{
    const RAW_CREATION_METHOD_NAME = 'create';
    const SNAPSHOT_THRESHOLD = 30; //can be overriden in child classes: snapshotThreshold()

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var int
     */
    private $playhead = DomainEvent::DEFAULT_PLAYHEAD; //concurrency

    /**
     * @var null
     */
    private $snapShotVersion = null;

    /**
     * @var null
     */
    private $footPrint = null;

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function recordApplyPublish(DomainEvent $event)
    {
        $this->record($event);
        $this->apply($event);
        $this->publish($event);
        $this->snapshot();
    }

    /**
     * Creates a persisted Domain Event from a domain event interface, then processes it
     * @param IDomainEvent $event
     */
    public function process(IDomainEvent $event)
    {
        $parent = get_parent_class($this);
        $aggregateClass = $parent === Aggregate::class ? get_class($this) : $parent;
        $domainEvent = DomainEvent::create($event, $aggregateClass);
        $this->recordApplyPublish($domainEvent);
    }

    /**
     * @param $event
     * @return void
     * See also BBC:
     *   $this->events[] = DomainMessage::recordNow(
     *      $this->id(),
     *      $this->playhead,
     *      new Metadata([]),
     *      $event
     *   );
     */
    public function record(DomainEvent $event)
    {
        $this->playhead++;
        $event->setPlayHead($this->playhead);
        $this->events[] = $event;
    }

    /**
     * @param DomainEvent $event
     * @param bool $strict if true, will throw an exception if applier method is not found
     * @throws ChildEventException
     * @throws MissingApplierMethodException
     */
    public function apply(DomainEvent $event, $strict = false)
    {
        $childEvent = $event->getPayload();
        if (!$childEvent || !($childEvent instanceof IDomainEvent)) {
            throw new ChildEventException(sprintf("childEvent %s could not be built in %s", $event->getEventType(), get_class($this)));
        }

        $method = 'apply' . DomainEvent::getClassName($childEvent);
        if (!method_exists($this, $method)) {
            $msg = sprintf("method %s does not exist in %s", $method, get_class($this));
            if ($strict) {
                throw new MissingApplierMethodException($msg);
            }
            DomainLogger::instance()->alert($msg);
        } else {
            $this->$method($childEvent);
            $this->events[] = $event;
            $this->playhead = $event->getPlayHead();
        }
    }

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function publish(DomainEvent $event)
    {
        DomainEventPublisher::instance()->publish($event);
    }

    /**
     * @param bool $force
     */
    public function snapshot($force = false)
    {
        if (!$force) {

            if ((int)$this->playhead === 0) {
                return;
            }

            if (is_null($this->snapshotThreshold())) {
                return;
            }

            if ((int)$this->playhead % (int)$this->snapshotThreshold() !== 0) {
                return;
            }

        }

        DomainSnapshotPublisher::instance()->snapshot($this);
    }

    /**
     * @return array
     */
    public function recorded()
    {
        $events = $this->events;
        $this->clearEvents();
        return $events;
    }

    /**
     * @return int
     */
    public function playhead()
    {
        return $this->playhead;
    }

    /**
     * @return int
     */
    public function lastEventPlayhead()
    {
        if (count($this->events) === 0) {
            return 0;
        }

        $lastEvent = end($this->events);
        return $lastEvent->getPlayHead();
    }

    /**
     * @return bool
     */
    public function playHeadValid()
    {
        return $this->playhead === $this->lastEventPlayhead();
    }

    /**
     * @return void
     */
    public function clearEvents()
    {
        $this->events = [];
    }

    /**
     * @param EventStream $history
     * @param null $aggregate
     * @return Aggregate
     */
    public static function reconstitute(EventStream $history, $aggregate = null)
    {
        $aggregate = $aggregate ?? forward_static_call([get_called_class(), self::RAW_CREATION_METHOD_NAME], $history->aggregateId());
        foreach ($history as $event) {
            $aggregate->apply($event);
        }
        return $aggregate;
    }

    /**
     * @return array
     * Overrride if needed for debug purposes
     */
    public function toArray()
    {
        return [];
    }

    /**
     * Override this with custom value in child class if needed.
     * If null: no snapshot.
     * @return int|null
     */
    protected function snapshotThreshold()
    {
        $class = get_called_class();
        return $class::SNAPSHOT_THRESHOLD;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setSnapshotVersion($version)
    {
        $this->snapShotVersion = $version;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSnapshotVersion()
    {
        return $this->snapShotVersion;
    }

    /**
     * @return null
     */
    public function getFootPrint()
    {
        return $this->footPrint;
    }

    /**
     * Warning: if you use memory_get_usage to detect and compute footprint,
     * in a docker container context it can lead to some hazardous result.
     *
     * @param null $footPrint
     * @param bool $convert
     */
    public function setFootPrint($footPrint, $convert = true)
    {
        $this->footPrint = $convert ? self::convertMem($footPrint) : $footPrint;
    }

    /**
     * @param $size
     * @return string
     */
    public static function convertMem($size)
    {
        if ($size === 0) {
            return $size;
        }

        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
    }

}
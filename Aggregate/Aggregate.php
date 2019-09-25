<?php

namespace Xefiji\Seasons\Aggregate;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\ChildEventException;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\DomainEventPublisher;
use Xefiji\Seasons\Event\EventStream;
use Xefiji\Seasons\Event\IDomainEvent;
use Xefiji\Seasons\Exception\MissingApplierMethodException;

/**
 * Class Aggregate (Root)
 * @package Xefiji\Seasons
 */
abstract class Aggregate implements AggregateInterface
{
    const RAW_CREATION_METHOD_NAME = 'create';

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var int
     */
    private $playhead = DomainEvent::DEFAULT_PLAYHEAD; //concurrency

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function recordApplyPublish(DomainEvent $event)
    {
        $this->record($event);
        $this->apply($event);
        $this->publish($event);
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
     * @return Aggregate
     */
    public static function reconstitute(EventStream $history)
    {
        $aggregate = forward_static_call([get_called_class(), self::RAW_CREATION_METHOD_NAME], $history->aggregateId());
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

}
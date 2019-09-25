<?php

namespace Xefiji\Seasons\Projection;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\DomainEvent;

/**
 * Class DefaultProjector
 * @package Xefiji\Seasons\Projection
 */
class DefaultProjector implements Projector
{
    /**
     * @var Projection[]
     */
    private $projections = [];

    /**
     * @param Projection[] $projections
     */
    public function register($projections)
    {
        foreach ($projections as $projection) {
            $this->registerOne($projection);
        }
    }

    /**
     * @param $projection
     */
    public function registerOne(Projection $projection)
    {
        foreach ($projection->listenTo() as $event) {
            $this->projections[$event][] = $projection;
        }
    }

    /**
     * Unoptimized way of unregistering projections.
     * In vivo, should not be used that often (mostly on script side, to filter some specific projections)
     *
     * @param Projection[] $projections
     */
    public function unregister($projections)
    {
        foreach ($this->projections as $event => $registeredProjections) {
            foreach ($registeredProjections as $k => $registeredProjection) {
                foreach ($projections as $projection) {
                    if (get_class($registeredProjection) !== $projection) {
                        unset($this->projections[$event][$k]);
                        $this->projections[$event] = array_values($this->projections[$event]);
                    }
                }
            }
        }
    }

    /**
     * @param Projection $projection
     */
    public function filter($projection)
    {
        $this->unregister([$projection]);
    }

    /**
     * @param DomainEvent[] $events
     * @todo find a better way that doesn't involve this double loop
     */
    public function project($events)
    {
        if (!is_array($events)) {
            $events = [$events];
        }

        array_walk($events, function (&$event) {
            /**@var DomainEvent $event * */
            $eventFullName = $event->getFullName();
            if (isset($this->projections[$eventFullName])) {
                foreach ($this->projections[$eventFullName] as $projection) {
                    try {
                        /**@var Projection $projection * */
                        $projection->project($event);
                    } catch (\Exception $e) {
                        DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())), [$event]);
                    }
                }
            }
        });
    }
}
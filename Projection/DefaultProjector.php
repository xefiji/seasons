<?php

namespace Xefiji\Seasons\Projection;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\IDomainEvent;
use Xefiji\Seasons\Exception\DomainLogicException;

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
     * @param IDomainEvent $event
     * @throws \Exception
     */
    public function project($event): void
    {
        $eventFullName = $this->getEventFullName($event);
        if (isset($this->projections[$eventFullName])) {
            foreach ($this->projections[$eventFullName] as $projection) {
                try {
                    /**@var Projection $projection * */
                    $projection->project($event);
                } catch (\Exception $e) {
                    DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())), [$event]);
                    throw $e; //allow fail to allow retry, in a batch processing context for example
                }
            }
        }
    }

    /**
     * @param $event
     * @return string
     * @throws DomainLogicException
     */
    private function getEventFullName($event): string
    {
        if ($event instanceof IDomainEvent) {
            return get_class($event);
        }
        if ($event instanceof DomainEvent) {
            return $event->getFullName();
        }

        throw new DomainLogicException(sprintf("Event %s 's name cannot be resolved", get_class($event)));
    }

    /**
     * @param null $projectionName
     * @param null $aggregateId
     * @throws \Exception
     */
    public function resetProjections($projectionName = null, $aggregateId = null): void
    {
        foreach ($this->getProjections() as $projection) {
            try {
                if ($projectionName && get_class($projection) !== $projectionName) {
                    continue;
                }

                $this->reset($projection, $aggregateId);
            } catch (\Exception $e) {
                DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
                throw $e;
            }
        }
    }

    /**
     * @param Projection $projection
     * @param null $aggregateId
     */
    public function reset(Projection $projection, $aggregateId = null): void
    {
        $projection->reset($aggregateId);
    }

    /**
     * As uniq
     * @return array
     */
    public function getProjections(): array
    {
        //make uniq
        $res = [];
        foreach ($this->projections as $event => $projections) {
            foreach ($projections as $projection) {
                $res[get_class($projection)] = $projection;
            }
        }
        return $res;
    }
}
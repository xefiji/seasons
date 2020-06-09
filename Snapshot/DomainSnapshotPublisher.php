<?php

namespace Xefiji\Seasons\Snapshot;

use Xefiji\Seasons\Aggregate\Aggregate;


/**
 * Class DomainSnapshotPublisher
 * @package Xefiji\Seasons\Event
 */
class DomainSnapshotPublisher
{
    /**
     * @var SnapshotSubscriber[]
     */
    private $subscribers;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * DomainSnapshotPublisher constructor.
     */
    private function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * Singleton
     * @return DomainSnapshotPublisher|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param SnapshotSubscriber $snapshotSubscriber
     * @return void
     */
    public function subcribe(SnapshotSubscriber $snapshotSubscriber)
    {
        $this->subscribers[] = $snapshotSubscriber;
    }

    /**
     * @param SnapshotSubscriber $snapshotSubscriber
     * @return void
     */
    public function subcribeOne(SnapshotSubscriber $snapshotSubscriber)
    {
        $this->subscribers = [$snapshotSubscriber];
    }

    /**
     * @param SnapshotSubscriber $snapshotSubscriber
     * @return void
     */
    public function unsubscribe(SnapshotSubscriber $snapshotSubscriber)
    {
        $subscribers = [];
        while ($subscriber = array_shift($this->subscribers)) {
            if ($subscriber != $snapshotSubscriber) {
                $subscribers[] = $subscriber;
            }
        }

        $this->subscribers = $subscribers;
    }

    /**
     * @param Aggregate $aggregate
     */
    public function snapshot(Aggregate $aggregate)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribed($aggregate)) {
                $subscriber->handle($aggregate);
            }
        }
    }


    /**
     * @throws \Exception
     */
    public function __clone()
    {
        throw new \Exception("Why would you clone a singleton ?");
    }

    /**
     * @throws \Exception
     */
    private function __wakeup()
    {
        throw new \Exception("Why would you unserialize a singleton ?");
    }
}
<?php

namespace Xefiji\Seasons\Messaging;

use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Exception\DomainLogicException;
use Xefiji\Seasons\Helper\Date;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventStore;

/**
 * Class NotificationService
 * @package Xefiji\Seasons\Message
 */
class NotificationService
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var PublishedMessageTracker
     */
    private $publishedMessageTracker;

    /**
     * @var MessageProducer
     */
    private $messageProducer;

    /**
     * @var MessageConsumer
     */
    private $messageConsumer;

    /**
     * NotificationService constructor.
     * @param EventStore $eventStore
     * @param PublishedMessageTracker $publishedMessageTracker
     * @param MessageProducer $messageProducer
     * @param MessageConsumer $messageConsumer
     */
    public function __construct(EventStore $eventStore, PublishedMessageTracker $publishedMessageTracker, MessageProducer $messageProducer, MessageConsumer $messageConsumer)
    {
        $this->eventStore = $eventStore;
        $this->publishedMessageTracker = $publishedMessageTracker;
        $this->messageProducer = $messageProducer;
        $this->messageConsumer = $messageConsumer;
    }

    /**
     * @param $exchangeName
     * @param null $ttl
     */
    public function subscribe($exchangeName, $ttl = null)
    {
        $this->messageConsumer->open($exchangeName);
        $this->messageConsumer->receive($exchangeName, $ttl);
    }

    public function publishFor(AggregateId $aggregateId, $exchangeName)
    {
        $notifs = $this->listAllFor($aggregateId);
        if (!$notifs) {
            return 0;
        }

        $this->messageProducer->open($exchangeName);
        $publishedMessages = 0;

        try {
            foreach ($notifs as $notif) {
                $this->publish($exchangeName, $notif, $this->messageProducer);
                $publishedMessages++;
            }
        } catch (\Exception $e) {
            DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
        }

        $this->messageProducer->close($exchangeName);
        return $publishedMessages;
    }

    /**
     * @param $exchangeName
     * @param bool $track
     * @return int
     */
    public function publishNotifs($exchangeName, $track = true)
    {
        $mostRecentPublishedMessageId = $this->publishedMessageTracker->mostRecentPublishedMessageId($exchangeName);
        if (is_null($mostRecentPublishedMessageId)) {
            $this->initTracker($exchangeName);
            return 0;
        }

        $notifs = $this->listUnpublished($mostRecentPublishedMessageId, $exchangeName);
        if (!$notifs || $notifs->count() === 0) {
            return 0;
        }

        $this->messageProducer->open($exchangeName);
        $publishedMessages = 0;
        $lastPublishedNotifications = null;

        try {
            foreach ($notifs as $notif) {
                $lastPublishedNotifications = $this->publish($exchangeName, $notif, $this->messageProducer);
                $publishedMessages++;
            }
        } catch (\Exception $e) {
            DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
        } finally {
            $this->messageProducer->close($exchangeName);
        }

        if ($track) {
            $id = $lastPublishedNotifications ? $lastPublishedNotifications->getId() : null;
            $this->trackMostRecentPublishedMessage($this->publishedMessageTracker, $exchangeName, $id);
        }

        return $publishedMessages;
    }

    /**
     * @param $mostRecentPublishedId
     * @param $exchangeName
     * @return mixed
     * @throws DomainLogicException
     */
    private function listUnpublished($mostRecentPublishedId, $exchangeName)
    {
        switch ($exchangeName) {
            case DomainEvent::PUBLISH_NAME:
                return $this->eventStore->allEventsSince($mostRecentPublishedId);
                break;
            default:
                throw new DomainLogicException(sprintf("No service configured to find last unpublished for %s", $exchangeName));
        }
    }

    /**
     * @param AggregateId $aggregateId
     * @return mixed
     */
    private function listAllFor(AggregateId $aggregateId)
    {
        return $this->eventStore->getEventsFor($aggregateId);
    }

    /**
     * @param $exchangeName
     * @return void
     */
    private function initTracker($exchangeName)
    {
        if ($last = $this->eventStore->lastEvent()) {
            $this->trackMostRecentPublishedMessage($this->publishedMessageTracker, $exchangeName, $last->getId());
        }
    }

    /**
     * @param $exchangeName
     * @param DomainEvent $notif
     * @param MessageProducer $messageProducer
     * @return DomainEvent
     */
    private function publish($exchangeName, DomainEvent $notif, MessageProducer $messageProducer)
    {
        $createdAt = Date::cast($notif->getCreatedAt());
        if (!$createdAt) {
            throw new DomainLogicException("createdAt field cannot be null and must be of type " . \DateTimeImmutable::class);
        }

        $messageProducer->send(
            $notif, //might need to be serialized on producer side, depending on the transport choosen
            $exchangeName,
            $notif->getEventType(),
            $notif->getId(),
            $createdAt
        );

        return $notif;
    }

    /**
     * @param PublishedMessageTracker $publishedMessageTracker
     * @param $exchangeName
     * @param $notificationId
     */
    private function trackMostRecentPublishedMessage(PublishedMessageTracker $publishedMessageTracker, $exchangeName, $notificationId)
    {
        $publishedMessageTracker->trackMostRecentPublishedMessage($exchangeName, $notificationId);
    }
}
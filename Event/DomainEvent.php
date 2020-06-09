<?php

namespace Xefiji\Seasons\Event;

use Xefiji\Seasons\Aggregate\Aggregate;
use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Serializer\DomainSerializer;


/**
 * Class DomainEvent
 * @package Xefiji\Seasons
 *
 * "An event is a named object that represents some discreet change that occurred in your system.
 * It's usually modelled as a class with a collection of properties, giving just enough formation to be useful."
 *
 * Mainly used here as a DTO
 *
 * https://github.com/AxonFramework/AxonFramework/issues/890#issuecomment-468586157
 * https://github.com/broadway/broadway/issues/120
 */
class DomainEvent
{
    const DEFAULT_PLAYHEAD = -1;
    const PUBLISH_NAME = "events";

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var string
     */
    protected $aggregateClass;

    /**
     * @var string
     */
    protected $eventType;

    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $auteur;

    /**
     * @var int
     */
    protected $playhead;

    /**
     * @var DomainEventMetadata
     */
    protected $metadatas;

    /**
     * DomainEvent constructor.
     * @param AggregateId $aggregateId
     * @param $aggregateClass
     * @param DomainEventMetadata|null $metadatas
     * @param null $auteur
     * @param int $playhead
     */
    private function __construct($aggregateId, $aggregateClass, DomainEventMetadata $metadatas = null, $auteur = null, $playhead = self::DEFAULT_PLAYHEAD)
    {
        $this->aggregateId = $aggregateId;
        $this->aggregateClass = $aggregateClass;
        $this->eventType = self::getClassName($this);
        $this->createdAt = new \DateTimeImmutable();
        $this->metadatas = $metadatas;
        $this->playhead = $playhead;
        $this->auteur = $auteur;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @return DomainEventMetadata|null
     */
    public function getMetadatas()
    {
        return $this->metadatas;
    }

    /**
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @return string
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * @return int
     */
    public function getPlayHead()
    {
        return $this->playhead;
    }

    /**
     * @param $playhead
     * @return int
     * @todo anti pattern ?
     */
    public function setPlayHead($playhead)
    {
        $this->playhead = $playhead;
    }

    /**
     * @param $auteur
     * @todo anti pattern ?
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;
    }

    /**
     * @param $object
     * @return string
     */
    public static function getClassName($object)
    {
        $class = is_object($object) ? get_class($object) : $object;
        $parts = explode("\\", $class);
        $nbElements = count($parts);
        if ($nbElements < 1) {
            return $class;
        }

        return array_pop($parts);
    }

    /**
     * @param $object
     * @return string
     */
    public static function getNamespace($object)
    {
        $class = is_object($object) ? get_class($object) : $object;
        $parts = explode("\\", $class);
        $nbElements = count($parts);
        if ($nbElements < 2) {
            return $class;
        }

        $popped = array_slice($parts, 0, $nbElements - 1);
        return implode("\\", $popped);
    }

    /**
     * @param bool $normalize will return a deserialized, reconstructed and normalized object based on _class property
     * instead of a pure json payload
     * @todo avoid putting VOs in events: https://buildplease.com/pages/vos-in-events/
     * @return mixed
     */
    public function getPayload($normalize = true)
    {
        return DomainSerializer::instance()->unserialize(
            $this->metadatas->getPayload(),
            null,
            'json',
            $normalize
        );
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $payload = $this->getPayload(false);
        return $payload->_class;
    }

    /**
     * Only use for upgrade purpose or migrations!
     * Events should always be immutable...
     * @param $aggregateClass
     * @return $this
     * @deprecated
     */
    public function setAggregateClass($aggregateClass)
    {
        $this->aggregateClass = $aggregateClass;
        return $this;
    }

    /**
     * Only use for upgrade purpose or migrations!
     * Events should always be immutable...
     * @param $eventNamespace
     * @return $this
     * @deprecated
     */
    public function setEventNamespace($eventNamespace)
    {
        $this->eventNamespace = $eventNamespace;
        return $this;
    }

    /**
     * Only use for upgrade purpose or migrations!
     * Events should always be immutable...
     * @param $metadatas
     * @return $this
     */
    public function setMetaDatas($metadatas)
    {
        $this->metadatas = new DomainEventMetadata(DomainSerializer::instance()->serialize($metadatas));
        return $this;
    }

    /**
     * Takes an IDomainEvent interface and creates a DomainEvent, with serialized fields in metadatas
     * @param IDomainEvent $event
     * @return DomainEvent
     */
    public static function create(IDomainEvent $event, $aggregateClass)
    {
        if (is_null($event->createdAt())) {
            $event->setCreatedAt(new \DateTimeImmutable());
        }

        $domainEvent = new self(
            $event->aggregateId(),
            $aggregateClass,
            new DomainEventMetadata($event->serialize()),
            $event->by()
        );

        $domainEvent->eventType = self::getClassName($event);
        $domainEvent->createdAt = $event->createdAt();

        return $domainEvent;
    }
}

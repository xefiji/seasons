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
    const NEW_VERSION_METHOD_NAME = "setNewVersion";
    const GLOBAL_TO_CHILD_METHOD = "fromGlobalDomainEvent";

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
     * @var
     */
    protected $version = 0;


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
     * @param Aggregate $aggregate
     * @param null $metadatas
     * @param int $playhead
     * @param null $createdAt
     * @param null $auteur
     * @return DomainEvent
     * @todo possibly automatically, when metadatas is array, map var and key
     * @deprecated
     */
    public static function fromAggregate(Aggregate $aggregate, $metadatas = null, $createdAt = null, $auteur = null, $playhead = self::DEFAULT_PLAYHEAD)
    {
        $serializer = DomainSerializer::instance();
        $metadatas = !is_null($metadatas) ? new DomainEventMetadata($serializer->serialize($metadatas)) : new DomainEventMetadata($serializer->serialize($aggregate));
        $event = new self($aggregate->id(), get_class($aggregate), $metadatas, $auteur, $playhead);
        $event->eventType = self::getClassName(get_called_class());
        $event->createdAt = is_null($createdAt) ? new \DateTimeImmutable() : ($createdAt instanceof \DateTimeImmutable ? $createdAt : new \DateTimeImmutable($createdAt));
        if (method_exists(get_called_class(), self::NEW_VERSION_METHOD_NAME)) {
            $event->version = forward_static_call([get_called_class(), self::NEW_VERSION_METHOD_NAME]);
        }

        return $event;
    }

    /**
     * Casts a global domain event (coming from event store) to a Child one
     * @param DomainEvent $event
     * @return DomainEvent
     * Important: static call
     * @deprecated
     */
    public static function fromGlobalDomainEvent(DomainEvent $event)
    {
        $childCalledClass = get_called_class();
        $childEvent = new $childCalledClass($event->getAggregateId(), $event->getAggregateClass(), $event->getMetadatas(), $event->getAuteur(), $event->getPlayHead());
        $childEvent->eventType = $event->getEventType();
        $childEvent->id = $event->getId();
        $childEvent->createdAt = $event->getCreatedAt();
        $childEvent->version = $event->getVersion();

        return $childEvent;
    }

    /**
     * Same as \Xefiji\Seasons\Event\DomainEvent::fromGlobalDomainEvent but in an object context: $this->fromGlobalEventObject()
     * @return DomainEvent
     * Important: object call
     * @deprecated
     */
    public function fromGlobalEventObject()
    {
        return call_user_func([$this->getFullName(), "fromGlobalDomainEvent"], $this);
    }

    /**
     * @param DomainEvent $event
     * @return mixed|null
     * @deprecated
     */
    public static function domainToChildEvent(DomainEvent $event)
    {
        /**@var IDomainEvent $child * */
        $child = $event->getPayload();
        return $child;
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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

<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Event\EventConflictException;
use Xefiji\Seasons\Event\EventStore;
use Xefiji\Seasons\Event\EventStream;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class DoctrineEventStore
 * @package Xefiji\Seasons\Infrastructure\Doctrine
 */
class DoctrineEventStore implements EventStore
{
    use PersistenceCapability;
    
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * DoctrineEventStore constructor.
     * @param EntityManager $em
     * @todo there should be a serializer here
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param EventStream $eventStream
     * @throws EventConflictException
     * @throws \Exception
     */
    public function appendAll(EventStream $eventStream): void
    {
        $this->reOpen();
        $this->em->beginTransaction();
        try {
            foreach ($eventStream as $event) {
                $this->em->persist($event);
                $this->em->flush();
            };
            $this->em->commit();
        } catch (UniqueConstraintViolationException $e) {
            $this->rollback();
            throw new EventConflictException("Event conflict in " . __METHOD__);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        } finally {
            foreach ($eventStream as $event) {
                $this->em->detach($event);
            };
            $this->em->clear();
        }
    }

    /**
     * @param DomainEvent $domainEvent
     * @throws EventConflictException
     * @throws \Exception
     */
    public function append(DomainEvent $domainEvent): void
    {
        $this->reOpen();
        $this->em->beginTransaction();
        try {
            $this->em->persist($domainEvent);
            $this->em->flush();
            $this->em->commit();
        } catch (UniqueConstraintViolationException $e) {
            $this->rollback();
            throw new EventConflictException("Event conflict in " . __METHOD__);
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        } finally {
            $this->em->detach($domainEvent);
            $this->em->clear();
        }

    }

    /**
     * @return void
     */
    private function rollback(): void
    {
        $this->em->rollback();
        $this->em->clear();
    }

    public function getEventsFor(AggregateId $aggregateId, string $aggregateClass = null): EventStream
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead');

        if ($aggregateClass) {
            $qb->andWhere('d.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return new EventStream($aggregateId, $qb->getQuery()->getResult());
    }

    /**
     * @param AggregateId $aggregateId
     * @param int $eventId
     * @param string|null $aggregateClass
     * @return EventStream
     */
    public function getEventsForUntil(AggregateId $aggregateId, int $eventId, string $aggregateClass = null): EventStream
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId)
            ->andWhere('d.id <= :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead');

        if ($aggregateClass) {
            $qb->andWhere('d.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return new EventStream($aggregateId, $qb->getQuery()->getResult());
    }


    /**
     * @param AggregateId $aggregateId
     * @param int $playhead
     * @param string|null $aggregateClass
     * @return EventStream
     */
    public function getEventsSincePlayhead(AggregateId $aggregateId, int $playhead, string $aggregateClass = null): EventStream
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId)
            ->andWhere('d.playhead > :playhead')
            ->setParameter('playhead', $playhead)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead');

        if ($aggregateClass) {
            $qb->andWhere('d.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return new EventStream($aggregateId, $qb->getQuery()->getResult());
    }

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return IterableResult
     */
    public function iterateFor(AggregateId $aggregateId, string $aggregateClass = null): IterableResult
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead');

        if ($aggregateClass) {
            $qb->andWhere('d.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return $qb->getQuery()->iterate();
    }

    /**
     * @param $eventId
     * @return EventStream
     * @throws DomainLogicException
     */
    public function allEventsSince($eventId): EventStream
    {
        if (!is_int($eventId)) {
            throw new DomainLogicException("eventId should be a int");
        }

        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.id > :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead')
            ->getQuery();

        return new EventStream(null, $qb->getResult());
    }

    /**
     * @param $eventId
     * @return IterableResult
     * @throws DomainLogicException
     */
    public function iterateSince($eventId): IterableResult
    {
        if (!is_int($eventId)) {
            throw new DomainLogicException("eventId should be a int");
        }

        $iterator = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.id > :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead')
            ->getQuery()
            ->iterate();

        return $iterator;
    }

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function iterateSinceDate(\DateTimeImmutable $dateTimeImmutable): IterableResult
    {
        $iterator = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.createdAt >= :dateTime')
            ->setParameter('dateTime', $dateTimeImmutable)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead')
            ->getQuery()
            ->iterate();

        return $iterator;
    }

    /**
     * @return mixed
     */
    public function lastEvent()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->orderBy('d.id', 'DESC')
            ->addOrderBy('d.createdAt', 'DESC')
            ->addOrderBy('d.playhead', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $qb->getOneOrNullResult();
    }

    /**
     * @param \DateTimeImmutable $dateTimeImmutable
     * @return mixed
     */
    public function allEventsSinceDate(\DateTimeImmutable $dateTimeImmutable): EventStream
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd')
            ->where('d.createdAt >= :dateTime')
            ->setParameter('dateTime', $dateTimeImmutable)
            ->orderBy('d.id')
            ->addOrderBy('d.createdAt')
            ->addOrderBy('d.playhead')
            ->getQuery();

        return new EventStream(null, $qb->getResult());
    }

    /**
     * @param AggregateId $aggregateId
     * @param array $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return mixed
     */
    public function count(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(d)')
            ->from(DomainEvent::class, 'd');

        if ($aggregateId) {
            $qb->andWhere('d.aggregateId = :aggregateId')
                ->setParameter('aggregateId', $aggregateId);
        }

        if ($sinceId) {
            $qb->andWhere('d.id > :eventId')
                ->setParameter('eventId', $sinceId);
        }

        if ($events) {
            $eventTypes = array_map(function ($event) {
                return substr_replace($event, "", 0, strrpos($event, "\\") + 1);
            }, $events);
            $qb->andWhere('d.eventType IN (:eventTypes)')
                ->setParameter('eventTypes', $eventTypes);
        }

        if ($sinceDate) {
            $qb->andWhere('d.createdAt > :dateTime')
                ->setParameter('dateTime', $sinceDate);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param AggregateId|null $aggregateId
     * @param array $events
     * @param null $sinceId
     * @param \DateTimeImmutable|null $sinceDate
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function queryIterator(AggregateId $aggregateId = null, $events = [], $sinceId = null, \DateTimeImmutable $sinceDate = null): IterableResult
    {
        $iterator = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DomainEvent::class, 'd');

        if ($aggregateId) {
            $iterator->where('d.aggregateId = :aggregateId')
                ->setParameter('aggregateId', $aggregateId);
        }

        if ($sinceId) {
            $iterator->andWhere('d.id > :eventId')
                ->setParameter('eventId', $sinceId);
        }

        if ($events) {
            $eventTypes = array_map(function ($event) {
                return substr_replace($event, "", 0, strrpos($event, "\\") + 1);
            }, $events);
            $iterator->andWhere('d.eventType IN (:eventTypes)')
                ->setParameter('eventTypes', $eventTypes);
        }

        if ($sinceDate) {
            $iterator->andWhere('d.createdAt > :dateTime')
                ->setParameter('dateTime', $sinceDate);
        }

        return $iterator->getQuery()->iterate();
    }

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return bool
     */
    public function has(AggregateId $aggregateId, string $aggregateClass = null): bool
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(d)')
            ->from(DomainEvent::class, 'd')
            ->andWhere('d.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId->value());

        if ($aggregateClass) {
            $qb->andWhere('d.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        if ($res = $qb->getQuery()->getSingleScalarResult()) {
            return (int)$res > 0;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->em->clear();
        return $this;
    }
}
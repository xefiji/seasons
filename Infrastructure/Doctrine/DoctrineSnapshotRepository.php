<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;


use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Snapshot\Snapshot;
use Xefiji\Seasons\Snapshot\SnapshotRepository;
use Doctrine\ORM\EntityManager;

/**
 * Class DoctrineSnapshotRepository
 * @package Xefiji\Seasons\Infrastructure\Doctrine
 */
class DoctrineSnapshotRepository implements SnapshotRepository
{
    use PersistenceCapability;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * DoctrineSnapshotRepository constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return null|Snapshot
     */
    public function find(AggregateId $aggregateId, string $aggregateClass = null): ?Snapshot
    {
        $this->reOpen();
        $qb = $this->em->createQueryBuilder()
            ->select('s')
            ->from(Snapshot::class, 's')
            ->where('s.aggregateId = :aggregateId')
            ->setParameter('aggregateId', $aggregateId)
            ->orderBy('s.version', 'DESC')
            ->setMaxResults(1);

        if ($aggregateClass) {
            $qb->andWhere('s.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param AggregateId $aggregateId
     * @param string|null $aggregateClass
     * @return array
     */
    public function findAll(AggregateId $aggregateId = null, string $aggregateClass = null): array
    {
        $this->reOpen();
        $qb = $this->em->createQueryBuilder()
            ->select('s')
            ->from(Snapshot::class, 's')
            ->orderBy('s.version', 'DESC');

        if ($aggregateId) {
            $qb->andWhere('s.aggregateId = :aggregateId')
                ->setParameter('aggregateId', $aggregateId);
        }

        if ($aggregateClass) {
            $qb->andWhere('s.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * @param Snapshot $snapshot
     * @return void
     */
    public function save(Snapshot $snapshot): void
    {
        try {
            $this->em->persist($snapshot);
            $this->em->flush();
        } catch (\Exception $e) { //just log. Snapshotting should not disturb runtime execution flow.
            DomainLogger::instance()->error(
                sprintf(
                    "%s - %s - %s - %s - %s",
                    __CLASS__,
                    __FUNCTION__,
                    "Trying to persist an already existing snapshot: ",
                    $snapshot->getAggregateId(),
                    $snapshot->getVersion()
                )
            );
        } finally {
            $this->em->clear();
        }
    }

    /**
     * @param AggregateId|null $aggregateId
     * @param string|null $aggregateClass
     * @param null $version
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeFor(AggregateId $aggregateId = null, string $aggregateClass = null, $version = null): void
    {
        $this->reOpen();
        $qb = $this->em->createQueryBuilder()
            ->select('s')
            ->from(Snapshot::class, 's');

        if ($aggregateId) {
            $qb->andWhere('s.aggregateId = :aggregateId')
                ->setParameter('aggregateId', $aggregateId);
        }

        if ($aggregateClass) {
            $qb->andWhere('s.aggregateClass = :aggregateClass')
                ->setParameter('aggregateClass', $aggregateClass);
        }

        if ($version) {
            $qb->andWhere('s.version = :version')
                ->setParameter('version', $version);
        }

        foreach ($qb->getQuery()->getResult() as $snapshot) {
            $this->em->remove($snapshot);
        }

        $this->em->flush();
    }
}
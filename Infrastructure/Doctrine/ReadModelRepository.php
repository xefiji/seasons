<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;


use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ReadModelRepository
 * @package Xefiji\Seasons\Infrastructure\Doctrine
 */
class ReadModelRepository
{
    use PersistenceCapability;

    /**
     * ReadModelRepository constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em; //@todo fix this visibility problem.
    }

    /**
     * @param $entity
     * @return void
     */
    public function save($entity)
    {
        $this->reOpen();
        $this->em->persist($entity);
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @param $entity
     * @return void
     */
    public function remove($entity)
    {
        $this->reOpen();
        $this->em->remove($entity);
        $this->em->flush();
        $this->em->clear();
    }
}
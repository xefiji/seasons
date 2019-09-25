<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;


use Doctrine\ORM\EntityManager;

/**
 * Class ReadModelRepository
 * @package Xefiji\Seasons\Infrastructure\Doctrine
 */
class ReadModelRepository
{
    /**
     * ReadModelRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $entity
     * @return void
     */
    public function save($entity)
    {
        $this->reOpen();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param $entity
     * @return void
     */
    public function remove($entity)
    {
        $this->reOpen();
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @return void
     */
    protected function reOpen()
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }

}
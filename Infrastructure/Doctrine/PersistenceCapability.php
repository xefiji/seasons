<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class PersistenceCapability
 * @package Xefiji\Seasons\Infrastructure\Doctrine
 */
trait PersistenceCapability
{
    /**
     * @return void
     */
    public function reOpen(): void
    {
        if (false === $this->em->isOpen() || false === $this->em->getConnection()->ping()) {
            $try = 0;
            while (false === $this->em->isOpen()) {
                if ($try >= 3) {
                    break;
                }
                $this->em = $this->em->create(
                    $this->em->getConnection(),
                    $this->em->getConfiguration()
                );
                $try++;
                sleep(1);
            }
            if ($try >= 3) {
                throw new \LogicException(sprintf("[persistencecapability] max reopen try reached"));
            }
        }
    }
}
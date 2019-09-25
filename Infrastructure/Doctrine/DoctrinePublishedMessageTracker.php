<?php

namespace Xefiji\Seasons\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Messaging\PublishedMessage;
use Xefiji\Seasons\Messaging\PublishedMessageTracker;

/**
 * Class DoctrinePublishedMessageTracker
 * @package Xefiji\Seasons\Infrastructure\Messaging
 */
class DoctrinePublishedMessageTracker implements PublishedMessageTracker
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * DoctrineEventStore constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return void
     */
    protected function reOpen()
    {
        if (!$this->em->isOpen()) {
            $this->em = $this->em->create(
                $this->em->getConnection(),
                $this->em->getConfiguration()
            );
        }
    }

    /**
     * @param $exchangeName
     * @return mixed
     */
    public function mostRecentPublishedMessageId($exchangeName)
    {
        $this->reOpen();
        if ($messageTracked = $this->findOneByExchangeName($exchangeName)) {
            return $messageTracked->mostRecentPublishedMessageId();
        }

        return null;
    }

    /**
     * @param $exchangeName
     * @return mixed|null
     */
    private function findOneByExchangeName($exchangeName)
    {
        $this->reOpen();
        $this->em->clear(); //important!!

        $qb = $this->em->createQueryBuilder()
            ->select('pm')
            ->from(PublishedMessage::class, 'pm')
            ->where('pm.exchangeName = :exchangeName')
            ->setParameter('exchangeName', $exchangeName)
            ->getQuery()->useQueryCache(false)->useResultCache(false);

        try {
            $messageTracked = $qb->getOneOrNullResult();
            if (!$messageTracked) {
                return null;
            }
            return $messageTracked;
        } catch (NonUniqueResultException $e) {
            DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
            return null;
        }
    }

    /**
     * @param $exchangeName
     * @param $notificationId
     * @return void
     */
    public function trackMostRecentPublishedMessage($exchangeName, $notificationId)
    {
        if (!$notificationId) {
            return; //@todo oh yeah ?
        }

        $this->reOpen();

        $maxId = $notificationId;

        $lastMessageTracked = $this->findOneByExchangeName($exchangeName);
        if (is_null($lastMessageTracked)) {
            $lastMessageTracked = new PublishedMessage($exchangeName, $maxId);
        }

        $lastMessageTracked->updateMostRecentPublishedMessageId($maxId);
        $this->em->persist($lastMessageTracked);
        $this->em->flush($lastMessageTracked);
    }
}
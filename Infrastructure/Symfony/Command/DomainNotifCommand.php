<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Command;


use Xefiji\Seasons\Event\DomainEvent;
use Xefiji\Seasons\Exception\DomainLogicException;
use Xefiji\Seasons\Infrastructure\Uuid;
use Xefiji\Seasons\Aggregate\AggregateId;
use Xefiji\Seasons\Messaging\NotificationCommand;
use Xefiji\Seasons\Messaging\NotificationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DomainNotifCommand
 * @package Xefiji\Seasons\Infrastructure\Symfony\Command
 */
class DomainNotifCommand extends Command implements NotificationCommand
{
    use CommandWorkerCapability;

    const LOCK_NAME = "domain-events-broadcast";

    /** @var string */
    protected static $defaultName = 'domain:events:broadcast';

    /**
     * @var NotificationService
     */
    private $notifService;

    /**
     * @var bool
     */
    private $exit;

    /**
     * @var bool
     */
    private $mustLock;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notifService = $notificationService;
    }

    public function configure()
    {
        $this
            ->setDescription('Spreads events')
            ->addOption('no-track', null, InputOption::VALUE_NONE, 'Will not track')
            ->addOption('publish', null, InputOption::VALUE_NONE, 'Will publish')
            ->addOption('subscribe', null, InputOption::VALUE_NONE, 'Will subscribe')
            ->addOption('exchange', null, InputOption::VALUE_OPTIONAL, 'exchange name', DomainEvent::PUBLISH_NAME) //default exchange is "events"
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Time in seconds between two publish check', 2)
            ->addOption('aggregate', null, InputOption::VALUE_OPTIONAL, 'Specifiy an aggregate id to replay notifs for', null)
            ->addOption('time-limit', null, InputOption::VALUE_OPTIONAL, 'Time in seconds before long running script (worker) auto-exit', 600) //default 10min
            ->addOption('memory-limit', null, InputOption::VALUE_OPTIONAL, 'Memory before long running script (worker) auto-exit')
            ->addOption('cron', null, InputOption::VALUE_NONE, 'If setted, will not loop. To run as cron managed script')
            ->addOption('no-exit', null, InputOption::VALUE_NONE, 'Exit or not in cron capability')
            ->addOption('no-lock', null, InputOption::VALUE_NONE, 'Will skip lock handling if setted');
    }

    /**
     * @param null $lockName
     * @return bool
     * @deprecated use \Xefiji\Seasons\Infrastructure\Symfony\Command\CommandWorkerCapability::lockIfNeeded
     */
    private function lockIfNeeded($lockName = null)
    {
        if ($this->isCron() &&
            $this->mustLock &&
            $this->hasLockComponent() &&
            $this->setLock($lockName ?? self::LOCK_NAME)) {
            return $this->lockAcquire();
        }
        return true;
    }

    /**
     * @param $lockName
     * @throws DomainLogicException
     * @deprecated use \Xefiji\Seasons\Infrastructure\Symfony\Command\CommandWorkerCapability::guardLock
     */
    private function guardLock($lockName)
    {
        if (false === $this->lockIfNeeded($lockName)) {
            $this->io->error(sprintf("Command is already in process: %s for %s ", $this->getName(), $lockName));
            $this->end($this->exit);
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initialize($input, $output)
            ->start();

        if (!$input->getOption('publish') && !$input->getOption('subscribe')) {
            throw new \Exception('No method pub or sub specified');
        }

        $this->exit = !$input->getOption('no-exit');
        $this->mustLock = !$input->getOption('no-lock');

        if ($input->getOption('publish')) {
            if ($input->getOption('aggregate')) {
                $this->publishFor(Uuid::fromString($input->getOption('aggregate')));
            } else {
                $this->publish();
            }
        } elseif ($input->getOption('subscribe')) {
            $this->subscribe();
        }
    }

    /**
     * Main objective:
     * - get last events recorded in event store
     * - publish it (broadcasting) in a message queue
     * - track last published id
     */
    public function publish()
    {
        $this->io->title("publishing with {$this->sleep} seconds delay until {$this->getTimeLimit()}");
        $lockName = sprintf("%s-publish-%s", self::LOCK_NAME, $this->input->getOption('exchange'));
        $this->guardLock($lockName);

        $this->process(function () {
            $this->notifService->publishNotifs($this->input->getOption('exchange'), !$this->input->getOption('no-track'));
        }, $this->exit);
    }


    public function subscribe()
    {
        $exchange = $this->input->getOption('exchange');
        $lockName = sprintf("%s-subscribe-%s", self::LOCK_NAME, $exchange);
        $this->guardLock($lockName);

        $this->io->title("suscribed to {$exchange} with {$this->sleep} seconds delay until {$this->getTimeLimit()}");

        $this->process(function () use ($exchange) {
            $this->notifService->subscribe($exchange, $this->finishesAt);
        }, $this->exit);
    }

    /**
     * @param AggregateId $aggregateId
     */
    public function publishFor(AggregateId $aggregateId)
    {
        $exchange = $this->input->getOption('exchange');
        $lockName = sprintf("%s-publishFor-%s", self::LOCK_NAME, $exchange);
        $this->guardLock($lockName);

        $this->output->writeln("<info>publishing to {$exchange} exchange for aggregate {$aggregateId->value()}</info>");
        try {
            $this->notifService->publishFor($aggregateId, $exchange);
        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
<?php

namespace Xefiji\Seasons\Infrastructure\Symfony\Command;


use Xefiji\Seasons\Benchmark;
use Xefiji\Seasons\Helper\Date;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class CommandWorkerCapability
 * @package Xefiji\Seasons\Infrastructure\Symfony\Command
 */
trait CommandWorkerCapability
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \DateTimeImmutable
     */
    private $finishesAt;

    /**
     * @var integer
     */
    private $sleep = 0;

    /**
     * @var bool
     */
    private $workerMode = true;

    /**
     * @var int
     */
    private $memoryLimit;

    /**
     * @var LockInterface
     */
    private $lock;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return CommandWorkerCapability
     */
    protected function initialize(InputInterface $input, OutputInterface $output): self
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;
        $this->input = $input;

        if ($this->input->hasOption('sleep')) {
            $this->sleep = $this->input->getOption('sleep');
        }

        if ($this->input->hasOption('cron')) {
            $this->workerMode = !$this->input->getOption('cron');
        }

        if ($this->input->hasOption('time-limit')) {
            $tmp = new \DateTime();
            $tmp->modify(sprintf("+%d seconds", $this->input->getOption('time-limit')));
            $this->finishesAt = \DateTimeImmutable::createFromMutable($tmp);
            unset($finishesAt);
        }

        if ($this->input->hasOption('memory-limit') && !is_null($this->input->getOption('memory-limit'))) {
            $this->memoryLimit = $this->convertToBytes($this->input->getOption('memory-limit'));
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function start(): void
    {
        Benchmark::instance()->start();
    }

    /**
     * @return bool
     */
    protected function isOver(): bool
    {
        return !is_null($this->finishesAt) && $this->finishesAt <= new \DateTimeImmutable();
    }


    /**
     * @param bool $exit
     * @throws \Xefiji\Seasons\Exception\DomainLogicException
     */
    protected function end($exit = true): void
    {
        Benchmark::instance()->end();
        $this->io->newLine();
        $this->io->success("Execution time " . Benchmark::instance()->toMinutes() . " minutes");
        if ($exit) {
            exit(0);
        }
    }

    /**
     * @return string
     */
    protected function getTimeLimit(): string
    {
        return $this->finishesAt ? $this->finishesAt->format("H:i:s") : 'no time limit';
    }

    /**
     * @return bool
     */
    protected function isWorker(): bool
    {
        return $this->workerMode;
    }

    /**
     * @return bool
     */
    protected function isCron(): bool
    {
        return !$this->workerMode;
    }

    /**
     * @return string
     */
    protected function getMode(): string
    {
        return $this->isCron() ? 'cron' : 'worker';
    }

    /**
     * @return bool
     */
    protected function memoryExceeded(): bool
    {
        return !is_null($this->memoryLimit) && memory_get_usage(true) > $this->memoryLimit;
    }

    /**
     * Thanks to Samuel Roze...
     * \Symfony\Component\Messenger\Command\ConsumeMessagesCommand::convertToBytes
     * @param string $memoryLimit
     * @return int
     */
    private function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = strtolower($memoryLimit);
        $max = strtolower(ltrim($memoryLimit, '+'));
        if (0 === strpos($max, '0x')) {
            $max = \intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = \intval($max, 8);
        } else {
            $max = (int)$max;
        }

        switch (substr(rtrim($memoryLimit, 'b'), -1)) {
            case 't':
                $max *= 1024;
            // no break
            case 'g':
                $max *= 1024;
            // no break
            case 'm':
                $max *= 1024;
            // no break
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    /**
     * @param callable $function
     * @param bool $exit
     * @throws \Xefiji\Seasons\Exception\DomainLogicException
     */
    protected function cron(callable $function, $exit = true): void
    {
        try {
            $function();
        } catch (\Exception $e) {
            $this->io->error($e->getMessage());
        } finally {
            $this->lockRelease();
            $this->end($exit);
        }

    }

    /**
     * @param callable $function
     */
    protected function work(callable $function): void
    {
        while (true) {
            if ($this->memoryExceeded()) {
                $this->io->error("Exiting because memory {$this->memoryLimit} limit exceeded");
                $this->end();
            }
            if ($this->isOver()) {
                $this->end();
            }
            try {
                $function();
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            } finally {
                sleep($this->sleep);
            }
        }
    }

    /**
     * @param callable $function
     * @param bool $exit
     */
    protected function process(callable $function, $exit = true): void
    {
        //short process
        if ($this->isCron()) {
            $this->cron($function, $exit);
        } //long running process
        elseif ($this->isWorker()) {
            $this->work($function);
        }
    }

    /**
     * Switch betweens lock components (default is symfony 3.4's one) and check if main class exists,
     * or if composer package is installed, or whatever rules you need to write.
     * @param string $componentName
     * @return bool
     */
    protected function hasLockComponent($componentName = "symfony/lock"): bool
    {
        switch ($componentName) {
            case "symfony/lock":
                return class_exists(Lock::class) ||
                    is_null(shell_exec(sprintf("composer show -a %s | grep -i 'not found'", $componentName)));
                break;
            //more components to check?
            //...
            default:
                return false;
        }
    }

    /**
     * Sets a locker to $lock attributes according to desired component
     * @param $lockName
     * @param array $options
     * @param string $componentName
     * @return bool
     */
    protected function setLock($lockName, $options = [], $componentName = "symfony/lock"): bool
    {
        switch ($componentName) {
            case "symfony/lock":
                $lockStoreClass = $options["lock_store_class"] ?? FlockStore::class;
                $store = new $lockStoreClass();
                $factory = new Factory($store);
                $ttl = $options["ttl"] ?? null;
                $autoRelease = $options["auto_release"] ?? null;
                $this->lock = $factory->createLock($lockName, $ttl, $autoRelease);
                return true;
                break;
            //more components to check?
            //...
            default:
                return false;
        }
    }

    /**
     * checks if lock has been acquired
     * @param string $componentName
     * @return bool
     */
    protected function lockIsAcquired($componentName = "symfony/lock"): bool
    {
        switch ($componentName) {
            case "symfony/lock":
                return $this->lock instanceof LockInterface && $this->lock->isAcquired();
                break;
            //more components to check?
            //...
            default:
                return false;
        }
    }

    /**
     * Releases current acquired lock
     * @param string $componentName
     * @return bool
     */
    protected function lockRelease($componentName = "symfony/lock"): bool
    {
        switch ($componentName) {
            case "symfony/lock":
                if ($this->lock instanceof LockInterface) {
                    return $this->lock->release();
                }
                return false;
                break;
            //more components to check?
            //...
            default:
                return false;
        }
    }

    /**
     * @param string $componentName
     * @return bool
     */
    protected function lockAcquire($componentName = "symfony/lock"): bool
    {
        switch ($componentName) {
            case "symfony/lock":
                if ($this->lock instanceof LockInterface) {
                    return $this->lock->acquire();
                }
                return false;
                break;
            //more components to check?
            //...
            default:
                return false;
        }
    }

    /**
     * @param null $lockName
     * @param array $options
     * @return bool
     */
    protected function lockIfNeeded($lockName = null, $options = ["ttl" => 300.0, "auto_release" => true]): bool
    {
        if ($this->isCron() &&
            $this->hasLockComponent() &&
            $this->setLock($lockName ?? self::LOCK_NAME, $options)) {
            return $this->lockAcquire();
        }
        return true;
    }

    /**
     * @param $lockName
     * @throws \Exception
     */
    protected function guardLock($lockName)
    {
        if (false === $this->lockIfNeeded($lockName)) {
            throw new \Exception(sprintf("Command is already in process: %s for %s ", $this->getName(), $lockName));
        }
    }

    /**
     * Checks if it has reached the desired hour and if callback method satisfies it
     * @param array $hours
     * @param callable|null $callback an additional custom callback methods, returning a boolean value
     * @return bool
     */
    protected function canRun(array $hours, callable $callback = null): bool
    {
        if ($this->isCron() && count($hours) === 0) {
            throw new \LogicException("You must specify the time at which the script will actually run");
        }

        $now = new \DateTimeImmutable();

        //check hours
        foreach ($hours as $hour) {
            try {
                $toReach = new \DateTimeImmutable(date(Date::FORMAT_US) . " " . $hour);

                //have we reached this time ?
                $reached = $now >= $toReach;
                if (!$reached) {
                    continue;
                }

                //are we way to far from the reached time ? (lets say more than 1hour)
                $tooFar = $now >= $toReach->modify("+1 hour");
                if ($tooFar) {
                    continue;
                }

                if (null !== $callback) {
                    $done = $callback($now, $toReach);
                    if ($done) {
                        continue;
                    }
                }

                return true;
            } catch (\Exception $e) {
                $this->io->error(sprintf("failed parsing hours %s: %s", $hour, $e->getMessage()));
            }
        }

        return false;
    }
}

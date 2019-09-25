<?php

namespace Xefiji\Seasons\Bus;

use Xefiji\Seasons\Command\Command;
use Xefiji\Seasons\Command\CommandHandler;
use Xefiji\Seasons\Command\GroupCommandHandler;


/**
 * Interface CommandHandler
 * @package Xefiji\Seasons
 *
 *"It’s like a router that maps a Command to a Command Handler."
 */
interface CommandBus
{
    /**
     * @param Command $command
     */
    public function dispatch(Command $command);


    /**
     * @param CommandHandler $handler
     */
    public function subscribe(CommandHandler $handler);

    /**
     * @param GroupCommandHandler $handler
     * @return mixed
     */
    public function groupSubscribe(GroupCommandHandler $handler);
}
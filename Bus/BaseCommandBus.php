<?php

namespace Xefiji\Seasons\Bus;

use Xefiji\Seasons\Command\Command;
use Xefiji\Seasons\Command\CommandHandler;
use Xefiji\Seasons\Command\GroupCommandHandler;


/**
 * Class BaseCommandBus
 * @package Xefiji\Seasons\Bus
 */
final class BaseCommandBus implements CommandBus
{
    /**
     * @var CommandHandler[]
     */
    private $commandHandlers = [];

    /**
     * @var Command[]
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $isDispatching = false;

    /**
     * @param CommandHandler $handler
     * @return $this
     */
    public function subscribe(CommandHandler $handler)
    {
        $this->commandHandlers[] = $handler;
        return $this;
    }

    /**
     * @param Command $command
     * @return mixed
     */
    public function dispatch(Command $command)
    {
        $this->queue[] = $command;

        if (!$this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->queue)) {
                    foreach ($this->commandHandlers as $handler) {

                        /*One command, one handler*/
                        if ($handler instanceof CommandHandler) {
                            if ($handler->listenTo() !== get_class($command)) {
                                continue;
                            }
                            $handler->handle($command);
                        }

                        /*One handler, multiple commands (throug a method resolver function)*/
                        if ($handler instanceof GroupCommandHandler) {
                            if (!in_array(get_class($command), $handler->listenTo())) {
                                continue;
                            }
                            $handler->handle($command); //will resolve handler method name
                        }
                    }
                }
            } finally {
                $this->isDispatching = false;
            }
        }
    }

    /**
     * @param GroupCommandHandler $handler
     * @return mixed
     */
    public function groupSubscribe(GroupCommandHandler $handler)
    {
        $this->commandHandlers[] = $handler;
        return $this;
    }
}

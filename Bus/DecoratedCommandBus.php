<?php

namespace Xefiji\Seasons\Bus;

use Xefiji\Seasons\Command\Command;
use Xefiji\Seasons\Command\CommandHandler;
use Xefiji\Seasons\Command\GroupCommandHandler;


/**
 * Class DecoratedCommandBus
 * @package Xefiji\Seasons\Bus
 *
 * "The major advantage of this approach is that none of the specialized command bus implementations
 * needs to know about any of the other command buses. It only takes care of its own business
 * and then hands the command over to the next command bus.
 * The command bus object is also open for extension, and closed for modification."
 */
final class DecoratedCommandBus implements CommandBus
{
    private $baseCommandBus;

    public function __construct(BaseCommandBus $baseCommandBus)
    {
        $this->baseCommandBus = $baseCommandBus;
    }

    /**
     * @param Command $command
     */
    public function dispatch(Command $command)
    {
        /*
         * some code here
         */

        $this->baseCommandBus->dispatch($command);

        /*
         * some other code here
         */


        /*
         * EXAMPLE WITH A DB TRANSACTION:
            try {
                // start transaction
                $this->baseCommandBus->dispatch($command);
                // commit transaction
                } catch (Exception $exception) {
                // rollback transaction
            }
         */
    }

    /**
     * @param CommandHandler $handler
     */
    public function subscribe(CommandHandler $handler)
    {
        // TODO: Implement subscribe() method.
    }

    /**
     * @param GroupCommandHandler $handler
     * @return mixed
     */
    public function groupSubscribe(GroupCommandHandler $handler)
    {
        // TODO: Implement groupSubscribe() method.
    }
}
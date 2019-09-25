<?php

namespace Xefiji\Seasons\Command;


/**
 * Interface CommandHandler
 * Used for only one command.
 * For CommandHandlers handling multiple commands, see GroupCommandHandler
 * @package Xefiji\Seasons
 *
 * "It’s orchestrating the logic to process the Command and it’s not interacting in any ways with the user interface.
 * It’s here where our request is being dispatched and handled, where the things happen."
 *
 * "The relation between Command and Command Handler is 1:1.
 * A Command has only one Command Handler and vice versa."
 *
 * This is the Core sequence of steps a command handler follows:
 * - Validate the command on its own merits.
 * - Validate the command on the current state of the aggregate.
 * - If validation is successful, 0..n events (1 is Core).
 * - Attempt to persist the new events. If there's a concurrency conflict during this step, either give up, or retry things.
 *
 * Other definition of process:
 * - It receives a Command instance from the messaging infrastructure.
 * - It validates that the Command is a valid Command.
 * - It locates the aggregate instance that is the target of the Command. This may involve creating a new aggregate instance or locating an existing instance.
 * - It invokes the appropriate method on the aggregate instance passing in any parameters from the command.
 * - It persists the new state of the aggregate to storage.
 *
 * In handle method, don't make calls from other services. Better raise and dispatch events:
 * $user = User::signUp($command->emailAddress, $command->password);
 * $this->userRepository->add($user);
 * // create the event
 * $event = new UserSignedUp($user->id());
 * // dispatch the event
 * $this->eventDispatcher->dispatch($event);
 *
 * In Symfony, it's possible to register them all to commandbus with registerForAutoConfiguration
 *
 * In HANDLERS, we can switch between:
 * - CQRS without ES:
 *      - One could explicitly call repo add method:
 *          $this->missionRepository->add($mission);
 *      - Or run action that will retry automatically after $maxTries param:
 *          $action = new Retry($this->missionRepository, 'add', [$mission]);
 *          $action->run();
 *
 * - CQRS with ES:
 *      - Leave the aggregate raise its own event and let the subscribers handle it:
 *          - in open method, $mission->recordApplyPublish(MissionWasOpenedEvent::fromAggregate($mission));
 */
interface CommandHandler
{
    /**
     * @return string
     */
    public function listenTo();

    /**
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command);
}
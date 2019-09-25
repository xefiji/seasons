<?php

namespace Xefiji\Seasons\Command;

/**
 * Interface GroupCommandHandler
 * @package Xefiji\Seasons\Command
 *
 * used to group commands in the handlers and avoid
 * writing a ton of classes. for simple association one command => one command handler,
 * see basic CommandHandler interface.
 */
interface GroupCommandHandler
{
    const HANDLER_METHOD_PREFIX = "handle";

    /**
     * @return array
     */
    public function listenTo();

    /**
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command);

    /**
     * @param Command $command
     * @return mixed
     */
    public function resolveHandlerMethod(Command $command);
}
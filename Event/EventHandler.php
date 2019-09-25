<?php

namespace Xefiji\Seasons\Event;


/**
 * Interface EventHandler
 * @package Xefiji\Seasons\Event
 *
 * Will handle side effects (e.g send mail)
 * Only one difference with commandeHandlers:
 * the dispatcher can associate multiple handlers for each event
 */
interface EventHandler
{
    /**
     * @return string
     */
    public function listenTo();

    /**
     * @param IDomainEvent $event
     * @return void
     */
    public function handle(IDomainEvent $event);
}
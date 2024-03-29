<?php

namespace Xefiji\Seasons\Event;


use Xefiji\Seasons\Exception\DomainLogicException;

abstract class AbstractGroupEventHandler
{
    /**
     * @param DomainEvent $event
     * @return mixed|null
     */
    public function handle(DomainEvent $event)
    {
        if ($method = $this->resolveHandlerMethod($event)) {
            return call_user_func([$this, $method], $event->getPayload());
        }
        return null;
    }

    /**
     * @param DomainEvent $event
     * @return string
     * @throws \Exception
     */
    public function resolveHandlerMethod(DomainEvent $event)
    {
        $parts = explode("\\", $event->getFullName());
        $class = array_pop($parts);
        $method = GroupEventHandler::HANDLER_METHOD_PREFIX . $class;
        if (method_exists($this, $method)) {
            return $method;
        }

        throw new DomainLogicException("Method {$method} not implemented in " . __CLASS__);
    }
}
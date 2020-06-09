<?php

namespace Xefiji\Seasons\Command;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class AbstractGroupCommandHandler
 * @package Xefiji\Seasons\Command
 */
abstract class AbstractGroupCommandHandler
{
    /**
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command)
    {
        if ($method = $this->resolveHandlerMethod($command)) {
            return call_user_func([$this, $method], $command);
        }
        return null;

    }

    /**
     * @param Command $command
     * @return string
     * @throws \Exception
     */
    public function resolveHandlerMethod(Command $command)
    {
        $parts = explode("\\", get_class($command));
        $class = array_pop($parts);
        $method = GroupCommandHandler::HANDLER_METHOD_PREFIX . $class;
        if (method_exists($this, $method)) {
            return $method;
        }

        throw new DomainLogicException("Method {$method} not implemented in " . __CLASS__);
    }

    /**
     * @return array
     * Automatically returns all commands supposed to be listend to.
     * can be overriden if needed (cross BC commands maybe ?)
     */
    public function listenTo()
    {
        $commands = [];
        $calledClass = get_called_class();
        $namespace = substr($calledClass, 0, strrpos($calledClass, "\\"));
        foreach (get_class_methods($calledClass) as $method) {
            $pattern = "/^" . GroupCommandHandler::HANDLER_METHOD_PREFIX . "[a-zA-Z]+$/i";
            if (preg_match($pattern, $method, $matches)) {
                $method = $matches[0];
                $replace = "/^" . GroupCommandHandler::HANDLER_METHOD_PREFIX . "/";
                $commandName = $namespace . DIRECTORY_SEPARATOR . preg_replace($replace, "", $method);
                $commands[] = $this->winOrUnixName($commandName);
            }
        }

        return $commands;
    }

    /**
     * @param $namespace
     * @return mixed
     */
    private function winOrUnixName($namespace)
    {
        $unix = "\\";
        $win = "/";

        try {
            $res = [
                mb_substr_count($namespace, $unix) => $unix,
                mb_substr_count($namespace, $win) => $win,
            ];

            krsort($res);
            return str_replace(array_pop($res), array_shift($res), $namespace);

        } catch (\Exception $e) { //unix fallback
            DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
            return str_replace($win, $unix, $namespace);
        }
    }
}
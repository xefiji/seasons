<?php


namespace Xefiji\Seasons\State;


use Xefiji\Seasons\Exception\DomainLogicException;
use Xefiji\Seasons\Exception\StateNotFoundException;
use Xefiji\Seasons\Exception\StateTransitionException;

/**
 * Class StateProvider
 * @package Xefiji\Seasons\State
 */
class StateProvider
{
    /**
     * @var null|self
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var null|string
     */
    private $currentPackage = null;

    /**
     * @return StateProvider
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->packages = [];
        $this->currentPackage = null;
        $this->initialized = false;
        return $this;
    }

    /**
     * @param $filePath
     * @return StateProvider
     * @throws DomainLogicException
     */
    public function loadXML($filePath)
    {
        if (true === $this->initialized) {
            throw new DomainLogicException(sprintf("States are already initialized"));
        }

        if (!file_exists($filePath)) {
            throw new DomainLogicException(sprintf("No workflow file found at %s", $filePath));
        }

        $xml = simplexml_load_string(file_get_contents($filePath));
        $conf = json_decode(json_encode((array)$xml, true), true);

        $packages = !array_key_exists(0, $conf['packages']) ? [$conf['packages']] : $conf['packages']; //guaranty a multidimensional array
        foreach ($packages as &$package) {
            $states = [];
            foreach ($package['states'] as &$state) {
                if (isset($state["transitions"])) {
                    $state["transitions"] = !array_key_exists(0, $state["transitions"]) ? [$state['transitions']] : $state['transitions']; //guaranty an multidimensional array
                } else {
                    $state["transitions"] = [];
                }
                $states[$state["name"]] = $state;
            }
            $package['states'] = $states;
            $this->packages[$package['name']] = $package;
        }

        $this->initialized = true;

        return $this;
    }

    /**
     * @param $name
     * @return array
     */
    public function get($name = null)
    {
        $name = $name ?: $this->currentPackage;
        if (!isset($this->packages[$name])) {
            throw new DomainLogicException(sprintf("No workflow config found for %s", $name));
        }
        return $this->packages[$name];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->packages;
    }

    /**
     * Performs a check on required package,
     * and throw exception if compulsory keys or elements are not found
     * @param $name
     * @return StateProvider
     */
    public function check($name = null)
    {
        $name = $name ?: $this->currentPackage;
        $package = $this->get($name);

        if (!isset($package["states"]) || !count($package["states"])) {
            throw new DomainLogicException(sprintf("At least one state must be setted for %s", $name));
        }

        return $this;
    }

    /**
     * @throws DomainLogicException
     */
    public function checkAll()
    {
        foreach ($this->packages as $name => $package) {
            $this->check($name);
        }
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setCurrentPackage($name)
    {
        $this->currentPackage = $name;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function __clone()
    {
        throw new \Exception("Why would you clone a singleton ?");
    }

    /**
     * @throws \Exception
     */
    private function __wakeup()
    {
        throw new \Exception("Why would you unserialize a singleton ?");
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    ##########################################
    # STATE HANDLING
    ##########################################

    /**
     * @param $state
     * @param null $name
     * @return mixed
     * @throws DomainLogicException
     */
    public function getState($state, $name = null)
    {
        $package = $this->get($name ?: $this->currentPackage);
        if (isset($package['states'][$state])) {
            return $package['states'][$state];
        }
        throw new StateNotFoundException(sprintf("State %s not found for package %s", $state, $package['name']));
    }

    /**
     * @param $state
     * @param null $name
     * @return bool
     * @throws DomainLogicException
     */
    public function stateExists($state, $name = null)
    {
        try {
            $this->getState($state, $name);
            return true;
        } catch (StateNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws DomainLogicException
     */
    public function getInitial($name = null)
    {
        $package = $this->get($name ?: $this->currentPackage);
        return current($package["states"])["name"];
    }

    /**
     * @param $name
     * @return mixed
     * @throws DomainLogicException
     */
    public function getLast($name = null)
    {
        $package = $this->get($name ?: $this->currentPackage);
        return end($package["states"])["name"];
    }

    /**
     * @param $from
     * @param $to
     * @param bool $throw
     * @return bool
     * @throws DomainLogicException
     * @throws StateNotFoundException
     */
    public function canTransit($from, $to, $throw = false)
    {
        $from = $from instanceof State ? $from->getName() : $from;
        $to = $to instanceof State ? $to->getName() : $to;

        try {
            $from = $this->getState($from);
            $to = $this->getState($to);
        } catch (StateNotFoundException $e) {
            if ($throw) {
                throw $e;
            }
            return false;
        }

        if (isset($from['transitions'])) {
            foreach ($from['transitions'] as $transition) {
                if ($transition["to"] === $to["name"]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $from
     * @param $to
     * @throws StateTransitionException
     */
    public function guardStateTransition($from, $to)
    {
        if (!$this->canTransit($from, $to, true)) {
            $msg = 'Transition not allowed';
            throw new StateTransitionException(sprintf("%s from %s to %s ", $msg, $from, $to));
        }
    }

    ##########################################
    # TODOS
    ##########################################

    /**
     * It should be possible to add conf (xml, yml or db datas) for other packages after the first init
     * @todo
     */
    public function addConf()
    {

    }

    /**
     * One would need to ensure that PECL's yaml_parse is installed
     * @param $filePath
     * @todo
     */
    public function loadYML($filePath)
    {
    }

    /**
     * State machine could be configured on database side
     * @param $cnx
     * @todo
     */
    public function loadDB($cnx)
    {

    }
}
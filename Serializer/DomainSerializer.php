<?php

namespace Xefiji\Seasons\Serializer;

use Xefiji\Seasons\DomainLogger;
use Xefiji\Seasons\Exception\MissingClassInEvent;
use JMS\Serializer\Serializer;

/**
 * Class DomainSerializer
 * @package Xefiji\Seasons\Serializer
 */
class DomainSerializer
{
    /**@var Serializer $serializer * */
    private $serializer;

    private static $instance = null;

    private function __construct()
    {
        $this->serializer = null;
    }

    /**
     * @return DomainSerializer|null
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $data
     * @param string $format
     * @return string
     */
    public function serialize($data, $format = 'json')
    {
        if (!is_null($this->serializer)) {
            try {
                return $this->serializer->serialize($data, $format);
            } catch (\Exception $e) {
                DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
            }
        }

        return $this->defaultSerialise($data);
    }

    /**
     * @param $data
     * @return string
     */
    private function defaultSerialise($data)
    {
        return base64_encode(serialize($data));
    }

    /**
     * @param $data
     * @return mixed
     */
    private function defaultDeserialise($data)
    {
        return unserialize(base64_decode($data));
    }

    /**
     * @param $data
     * @param null $type
     * @param string $format
     * @param bool $normalize
     * @return mixed
     */
    public function unserialize($data, $type = null, $format = 'json', $normalize = true)
    {
        try {
            if (!is_null($this->serializer)) {
                switch ($format) {
                    case "json":
                        if ($json = json_decode($data)) {

                            //no class given: it's a non VO payload; just return it.
                            if (!isset($json->_class)) {
                                return $json;
                            }

                            //no denormalized object wanted: only json is returned
                            if (!$normalize) {
                                return $json;
                            }

                            if (!class_exists($json->_class)) {
                                throw new MissingClassInEvent("Needed class for deserialization not found in payload: " . $json->_class);
                            }

                            //deserialize data, but precising the given (an then known) type in param, or class name given on serialization
                            return $this->serializer->deserialize($data, ($type ?: $json->_class), $format);
                        }
                        break;
                    default:
                        throw new \Exception("No other serialization format plugged");
                }
            }
        } catch (MissingClassInEvent $e) {
            throw $e;
        } catch (\Exception $e) {
            DomainLogger::instance()->error((sprintf("%s - %s - %s - ", __CLASS__, __FUNCTION__, $e->getMessage())));
        }

        return $this->defaultDeserialise($data);
    }

    /**
     * @param $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }
}
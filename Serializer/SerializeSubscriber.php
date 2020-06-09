<?php

namespace Xefiji\Seasons\Serializer;


use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class SerializeSubscriber implements EventSubscriberInterface
{

    /**
     * Returns the events to which this class has subscribed.
     *
     * Return format:
     *     array(
     *         array('event' => 'the-event-name', 'method' => 'onEventName', 'class' => 'some-class', 'format' => 'json'),
     *         array(...),
     *     )
     *
     * The class may be omitted if the class wants to subscribe to events of all classes.
     * Same goes for the format key.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
            ],
            [
                'event' => 'serializer.pre_deserialize',
                'method' => 'onPreDeSerialize',
            ],
            [
                'event' => 'serializer.post_deserialize',
                'method' => 'onPostDeSerialize',
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {

    }

    /*
     * Add object class in payload
     * @todo switch on JMS Serializer version
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $versionThreshold = 2.0; //version number from which we can start switching from first to 2nd method.
        if (defined('JMS_SERIALIZER_VERSION')) {
            if (JMS_SERIALIZER_VERSION < $versionThreshold) {
                // <=  1.x
                $event->getVisitor()->addData(
                    '_class', get_class($event->getObject())
                );
                return;
            }
        }

        // > 1.x
        $event->getVisitor()->visitProperty(
            new StaticPropertyMetadata('', '_class', null), get_class($event->getObject())
        );

    }

    public function onPreDeSerialize(PreDeserializeEvent $event)
    {

    }

    public function onPostDeSerialize(ObjectEvent $event)
    {

    }
}
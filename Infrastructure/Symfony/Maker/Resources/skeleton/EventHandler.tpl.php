<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $event_full_name ?>;
use Xefiji\Seasons\Projection\DefaultProjector;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

final class <?= $class_name ?> implements MessageSubscriberInterface
{
    /**
     * @var DefaultProjector
     */
    private $projector;


    public function __construct(DefaultProjector $projector)
    {
        $this->projector = $projector;
    }

    /**
     * @param <?= $event_class_name ?> $event
     */
    public function __invoke(<?= $event_class_name ?> $event)
    {
        //$this->projector->project($event);
    }

    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        yield <?= $event_class_name ?>::class;
    }
}
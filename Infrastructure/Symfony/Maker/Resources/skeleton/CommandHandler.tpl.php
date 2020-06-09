<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $command_full_name ?>;
use <?= $aggregateNamespace ?>\EventStore<?= $aggregate_class_name ?>Repository;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

final class <?= $class_name ?> implements MessageSubscriberInterface
{
    private $eventStore<?= $aggregate_class_name ?>Repository;

    public function __construct(EventStore<?= $aggregate_class_name ?>Repository $eventStore<?= $aggregate_class_name ?>Repository)
    {
        $this->eventStore<?= $aggregate_class_name ?>Repository = $eventStore<?= $aggregate_class_name ?>Repository;
    }

    public function __invoke(<?= $command_class_name ?> $command)
    {
    }



    public static function getHandledMessages(): iterable
    {
        yield <?= $aggregate_class_name ?>Command::class;
        //yield OtherCommand::class => ['method' => 'handleOtherCommand'];
    }
}
<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Xefiji\Seasons\Event\DomainEventTrait;
use Xefiji\Seasons\Event\IDomainEvent;
use Xefiji\Seasons\Serializer\SerializeTrait;

final class <?= $class_name ?> implements IDomainEvent, \Serializable
{
    use DomainEventTrait, SerializeTrait;

    /** @var string */
    public $<?= strtolower($aggregate_class_name) ?>Id;

    /** @var string */
    public $someThing;

    /** @var string */
    public $someThingElse;


    public function __construct($<?= strtolower($aggregate_class_name) ?>Id, $someThing, $someThingElse)
    {
        $this-><?= strtolower($aggregate_class_name) ?>Id = $<?= strtolower($aggregate_class_name) ?>Id;
        $this->someThing = $someThing;
        $this->someThingElse = $someThingElse;

        $this->aggregateId = $<?= strtolower($aggregate_class_name) ?>Id;
        $this->by = $<?= strtolower($aggregate_class_name) ?>Id;
    }

    /**
     * @return string
     */
    public function get<?= $aggregate_class_name ?>Id(): string
    {
        return $this-><?= strtolower($aggregate_class_name) ?>Id;
    }

    /**
     * @return string
     */
    public function getSomething(): string
    {
        return $this->someThing;
    }

    /**
     * @return string
     */
    public function getSomethingElse(): string
    {
        return $this->someThingElse;
    }

}
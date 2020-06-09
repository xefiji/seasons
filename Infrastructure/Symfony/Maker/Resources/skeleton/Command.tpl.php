<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Xefiji\Seasons\Serializer\SerializeTrait;

final class <?= $class_name ?> implements \Serializable
{
    use SerializeTrait;

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
    }
}
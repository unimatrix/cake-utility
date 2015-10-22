<?php

namespace Borg\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

/**
 * Nullable
 * add null to null fields instead of ''
 *
 * @author Borg
 * @version 0.1
 */
class NullableBehavior extends Behavior
{
    public function beforeSave(Event $event, Entity $entity) {
        $schema = $this->_table->schema();
        foreach($entity->toArray() as $field => $value)
            if($schema->isNullable($field))
                if($value === '')
                    $entity->set($field, null);
    }
}
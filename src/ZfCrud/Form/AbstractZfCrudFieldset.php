<?php

namespace ZfCrud\Form;

use Zend\Form\Fieldset;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractZfCrudFieldset extends Fieldset {

    use FormFieldSetTrait;

    public function __construct(ObjectManager $objectManager, $entity) {

        $this->setup($objectManager, $entity);
    }

}

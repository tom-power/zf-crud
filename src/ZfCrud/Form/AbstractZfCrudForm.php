<?php

namespace ZfCrud\Form;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractZfCrudForm extends Form {

    use FormFieldSetTrait;

    public function __construct(ObjectManager $objectManager, $entity) {

        $this->setup($objectManager, $entity);
    }

}

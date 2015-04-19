<?php

namespace ZfCrud\Form;

use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;

trait FormFieldSetTrait {

    public function setup($objectManager, $entity) {

        $reflect = new \ReflectionClass($entity);

        $name = $reflect->getShortName();

        parent::__construct($name);

        $entityName = $reflect->getName();

        $this->setHydrator(new DoctrineEntity($objectManager, $entityName))
                ->setObject($entity);
    }

}

<?php

namespace ZfCrud\Form;

use Zend\Form\Fieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;

abstract class AbstractZfCrudFieldset extends Fieldset {


    public function __construct(ObjectManager $objectManager, $entity) {

        $reflect = new \ReflectionClass($entity);

        $name = $reflect->getShortName();

        parent::__construct($name);

        $entityName = $reflect->getName();

        $this->setHydrator(new DoctrineEntity($objectManager, $entityName))
                ->setObject($entity);

    }

}

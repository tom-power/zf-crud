<?php

namespace ZfCrud\Form;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\InputFilter\InputFilterProviderInterface;

abstract class AbstractZfCrudForm extends Form implements InputFilterProviderInterface {

    public function __construct(ObjectManager $objectManager, $entity) {

        $reflect = new \ReflectionClass($entity);

        $name = $reflect->getShortName();

        parent::__construct($name);

        $entityName = $reflect->getName();

        $this->setHydrator(new DoctrineEntity($objectManager, $entityName))
                ->setObject($entity);
    }

}

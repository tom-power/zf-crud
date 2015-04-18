<?php

namespace ZfCrud\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;

abstract class AbstractZfCrudController extends AbstractActionController {

    public function indexAction() {
        return $this->index();
    }

    public function viewAction() {
        return $this->edit(false);
    }

    public function editAction() {
        return $this->edit(true);
    }

    public function addAction() {
        return $this->editAction();
    }

    public function deleteAction() {
        return $this->delete();
    }

    // <editor-fold defaultstate="collapsed" desc="protected">
    // <editor-fold defaultstate="collapsed" desc="action methods">
    /**
     * Index action
     * @param type $pluralEntityName plural entity name if not like entity . s
     * @return ViewModel
     */
    protected function index($pluralEntityName = null) {
        $entities = $this->getRepository()->findBy(array(), array('id' => 'ASC'));
        $thisPluralEntityName = $pluralEntityName != null ? $pluralEntityName : $this->getEntityName() . 's';
        return new ViewModel(array($thisPluralEntityName => $entities));
    }

    /**
     *
     * @param boolean $edit form is editable
     * @param function $errorFunc anonymous function that should return a message on custom error and null if none, param = ZfCrud\Entity $entity
     * @param function $saveMessageFunc anonymous function that should return entity->CustomMessageRegistration if set and null if not, param = boolean $success, ZfCrud\Entity $entity
     * @return ViewModel
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function edit($edit, $errorFunc = null, $saveMessageFunc = null) {
        $entity = $this->getEntityFromRepo($this->params('id'));
        $form = $this->setUpForm($entity);
        $request = $this->getRequest();
        $postData = $this->updatePostData($request, $entity);
        if ($edit && $request->isPost()) {
            $form->setData($postData);
            if ($form->isValid()) {
                $form->bindValues();
                $customErrorMessage = $this->getCustomErrorMessage($errorFunc, $entity);
                if ($customErrorMessage != null) {
                    $this->flashMessenger()->addErrorMessage($customErrorMessage);
                } else {
                    $this->saveEntity($entity, $saveMessageFunc);
                }
                return $this->redirectIndex();
            }
        }
        return new ViewModel(array(
            $this->getEntityName() => $entity,
            'form' => $form
        ));
    }

    /**
     * Delete
     * @return type
     */
    protected function delete() {
        $entity = $this->getEntityFromRepo($this->params('id'));
        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
            $this->flashMessenger()->addSuccessMessage(ucfirst($this->getEntityName()) . ' deleted');
        }
        return $this->redirectIndex();
    }

    // <editor-fold defaultstate="collapsed" desc="action helpers">
    private function getCustomErrorMessage($errorFunc, $entity) {
        return $errorFunc != null ? $errorFunc($entity) : null;
    }

    public function saveEntity($entity, $saveMessageFunc) {
        try {
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
            $this->flashMessenger()->addSuccessMessage($this->getSaveMessage(true, $saveMessageFunc, $entity), $entity);
        } catch (\Doctrine\DBAL\DBALException $e) {
            if ($e->getPrevious()->getCode() === '23000') {
                $this->flashMessenger()->addErrorMessage($this->getSaveMessage(false, $saveMessageFunc, $entity), $entity);

                // Will output an SQLSTATE[23000] message, similar to:
                // Integrity constraint violation: 1062 Duplicate entry 'x'
                // ... for key 'UNIQ_BB4A8E30E7927C74'
            } else {
                throw $e;
            }
        }
    }

    private function getEntityFromRepo($id) {
        return $id > 0 ? $this->getRepository()->find($id) : $this->getEntity();
    }

    private function setUpForm($entity) {
        $form = $this->getForm();
//        $form->setHydrator(new DoctrineEntity($this->getEntityManager(), $this->getEntityStr()));
        $form->bind($entity);
        return $form;
    }

    private function getSaveMessage($success, $saveMessageFunc, $entity) {
        $message = null;
        if ($saveMessageFunc != null) {
            $message = $saveMessageFunc($success, $entity);
        }
        if ($message == null) {
            $saved = $success ? ' saved' : ' saved already';
            $message = ucfirst($this->getEntityName()) . $saved;
        }
        return $message;
    }

    public function updatePostData($request, $entity) {
        $postData = (array) $request->getPost();
        $classMetadata = $this->getEntityManager()->getClassMetadata(get_class($entity));
        foreach (array_keys($classMetadata->associationMappings) as $key) {
            if (!isset($postData[$key])) {
                $postData[$key] = array();
            }
        }
        return $postData;
    }

    public function redirectIndex() {
        $route = lcfirst($this->getNameSpaceRoot()) . '/default';
        return $this->redirect()->toRoute($route, array('controller' => $this->getEntityName(), 'action' => 'index'));
    }

    // </editor-fold>
    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="general helpers">
    protected function isAdmin() {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->zfcUserAuthentication()->getIdentity()->getUsername() == "admin";
        }
        return false;
    }

    protected function getRepository() {
        return $this->getEntityManager()->getRepository($this->getEntityStr());
    }

    protected function getForm() {
        $entityFormStr = $this->getEntityFormStr();
        return new $entityFormStr($this->getEntityManager());
    }

    protected function getEntity() {
        $entityStr = $this->getEntityStr();
        return new $entityStr;
    }

    protected function getEntityFormStr() {
        return $this->getNameSpaceRoot() . '\Form\\' . ucfirst($this->getEntityName()) . 'Form';
    }

    protected function getEntityStr() {
        return $this->getNameSpaceRoot() . '\Entity\\' . ucfirst($this->getEntityName());
    }

    protected function getNameSpaceRoot() {
        $reflect = new \ReflectionClass($this);
        $namespaceName = $reflect->getNamespaceName();
        return explode('\\', $namespaceName)[0];
    }

    protected function getEntityName() {
        $reflect = new \ReflectionClass($this);
        return lcfirst(str_replace('Controller', '', $reflect->getShortName()));
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="entityManger">
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Sets the EntityManager
     *
     * @param EntityManager $em
     * @access protected
     * @return PersonController
     */
    protected function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->entityManager = $em;
        return $this;
    }

    /**
     * Returns the EntityManager
     *
     * Fetches the EntityManager from ServiceLocator if it has not been initiated
     * and then returns it
     *
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager() {
        if (null === $this->entityManager) {
            $this->setEntityManager($this->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
        }
        return $this->entityManager;
    }

    // </editor-fold>
    // </editor-fold>
}

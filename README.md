##Introduction

Library to quickly add CRUD operations for doctrine entities in your ZF2 application.

##Installation

**Composer**

Add repository and the library to your composer.json:

```json
...
 "repositories":
            [
                {
                    "type": "vcs",
                    "url": "https://github.com/tom-power/ZfcTwitterBootstrap"
                },
                {
                    "type": "package",
                    "package": {
                        "name": "tom-power/zf-crud",
                        "version": "dev-master",
                        "source": {
                            "url": "https://github.com/tom-power/zf-crud",
                            "type": "git",
                            "reference": "master"
                        },
                        "autoload": {
                            "psr-0": {
                                "ZfCrud": "src"
                            }
                        }
                    }
                }
            ],
```

```json
"require": {
        ...
        "tom-power/zf-crud": "dev-master"
    }
```

##Usage

Controller:

```php
use ZfCrud\Controller\AbstractZfCrudController;

class TagController extends AbstractZfCrudController {

}
```

Entity:
```php
use ZfCrud\Entity\AbstractZfCrudEntity;

/**
 * Taggable
 *
 * @ORM\Table
 * @ORM\Entity
 */
class Tag extends AbstractZfCrudEntity {
```

Form:
```php
use ZfCrud\Form\AbstractZfCrudForm;

class TagForm extends AbstractZfCrudForm {

    public function __construct(ObjectManager $objectManager) {

        parent::__construct($objectManager, new \Taggable\Entity\Tag());

        $this->setAttribute('method', 'post');

        $tagFieldset = new TagFieldset($objectManager);

        $tagFieldset->setUseAsBaseFieldset(true);
        $this->add($tagFieldset);


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
```

Fieldset:
```php
use ZfCrud\Form\AbstractZfCrudFieldset;

class TagFieldsetSelect extends AbstractZfCrudFieldset {

    public function __construct(ObjectManager $objectManager) {

        parent::__construct($objectManager, new \Taggable\Entity\Tag());

        $this->add(array(
            'name' => 'id',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => array(
                'label' => 'Tag',
                'object_manager' => $objectManager,
                'target_class' => 'Taggable\Entity\Tag',
                'property' => 'name',
                'empty_option' => '',
            )
        ));
    }

```

see  [https://github.com/tom-power/zf-taggable](https://github.com/tom-power/zf-taggable) for an example application.
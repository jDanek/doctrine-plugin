Doctrine plugin
###############

Doctrine 2 integration plugin.

.. contents::


Requirements
************

- PHP 7.2+
- SunLight CMS 8


Usage
*****

See `Doctrine 2 documentation <https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/>`_.


Entity registration
===================

Entities need to be registered using the `doctrine.map_entities`_ event before they
can be used.


Table names
===========

Table are automatically prefixed with ``db.prefix`` as defined in the global *config.php*.

If no table name is explicitly specified, ``UnderscoreNamingStrategy`` is used to generate one.


Base entity class
=================

An optional base entity class is available. It provides several active record-esque
utility methods.


Static methods
--------------

- ``find($id)`` - try to find an instance by ID
- ``getRepository()`` - get repository for the entity class
- ``getEntityManager()`` - get the entity manager (protected)


Instance methods
----------------

- ``persist()`` - make the entity managed and persistent
- ``save()`` - persist and flush the entity to the database
- ``delete()`` - delete and flush this entity from the database


Example
-------

Entity definition
^^^^^^^^^^^^^^^^^

.. code:: php

   <?php

   namespace SunlightExtend\Example\Entity;

   use Doctrine\ORM\Mapping as ORM;
   use SunlightExtend\Doctrine\Entity;

   /**
    * @ORM\Table(name="example_foo")
    */
   class Foo extends Entity
   {
       /**
        * @ORM\Id
        * @ORM\GeneratedValue
        * @ORM\Column(type="integer")
        *
        * @var int
        */
       public $id;

       /**
        * @ORM\Column(type="string", length=64)
        *
        * @var string
        */
       public $name;

       function __construct(string $name)
       {
           $this->name = $name;
       }
   }


Basic usage
^^^^^^^^^^^

.. code:: php

   <?php

   // create a new entity
   $foo = new Foo('test');
   $foo->save();
   var_dump($foo->id);

   // find existing entities
   Foo::find(1); // by ID
   Foo::getRepository()->findBy(['name' => 'test']); // using repository

   // saving changes
   $foo->name = 'new name';
   $foo->save();

   // deleting entities
   $foo->delete();


Accessing the entity manager
============================

.. code:: php

   <?php

   use SunlightExtend\Doctrine\DoctrineBridge;

   $em = DoctrineBridge::getEntityManager();


Extend events
=============

``doctrine.init``
-----------------

Called when Doctrine is being initialized.

Arguments:

- ``connection`` - instance of ``Doctrine\DBAL\Connection``
- ``config`` - instance of ``Doctrine\ORM\Configuration``
- ``metadata_driver`` - instance of ``Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain``
- ``event_manager`` - instance of ``Doctrine\Common\EventManager``


``doctrine.map_entities``
-------------------------

Called when Doctrine entities should be registered.

Arguments:

- ``mapping`` - reference to the entity mapping array

Example:

.. code:: php

   <?php

   // register all entities in the Entity subnamespace using annotations
   $args['mapping']['annotation'][__NAMESPACE__ . '\\Entity\\'] = __DIR__ . '/Entity';

   // register all entities in the Entity subnamespace using XML files
   $args['mapping']['annotation'][__NAMESPACE__ . '\\Entity\\'] = __DIR__ . '/Resources/doctrine';

See:

- `Annotations reference <https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/annotations-reference.html>`_
- `XML mapping <https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/xml-mapping.html>`_


Doctrine console
================

Doctrine console can be accessed by clicking on the plugin's "Console" action
in "Administration - Plugins".

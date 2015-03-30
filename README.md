[![Build Status](https://travis-ci.org/derptest/phpmachinist.png?branch=master)](https://travis-ci.org/derptest/phpmachinist)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/688f98fb-99f9-4c37-b2d2-f817f846f992/mini.png)](https://insight.sensiolabs.com/projects/688f98fb-99f9-4c37-b2d2-f817f846f992)
# PHPMachinist: Testing Object Factory

## What???!??!
A Slightly less annoying way of creating database fixtures for PHP testing. It borrows heavily from
several projects:

* https://github.com/chriskite/phactory
* https://github.com/thoughtbot/factory_girl
* https://github.com/ccollins/milkman
* https://github.com/notahat/machinist

They're totally awesome. Just either in the wrong language, or didn't quite do what I wanted.

## Hip Hop VM (HHVM) Support

Hip Hop VM is supported and tested.  If you want to use the Doctrine store, it will require Doctrine ORM 2.4.x or
newer.

## Data Store Support

The following data stores are currently supported:

* PDO
    * MySQL
    * SQLite
* Doctrine 2 ORM

## Install
Add the package derptest/phpmachinist to your composer.json
For more information about Composer, please visit http://getcomposer.org

## Configure
Configuration of PHP Machinist happens in two steps:

1. Register data stores

    Registering data stores is done via either the static `Machinist::store()` method or the
`addStore()` method on a Machinist instance.  Both methods take the same parameters, a `StoreInterface`
instance and an optional name for that store.  If no name is given, it will default to `default`.
Below is an example of both:

    ```php
    <?php
    use DerpTest\Machinist\Machinist;
    use DerpTest\Machinist\Store\SqlStore;
    
    // This store will be referenced by the name "default"
    Machinist::store(SqlStore::fromPdo(new \PDO('sqlite::memory:')));
    
    // This store will be referenced by the name "non-default"
    Machinist::instance()->addStore(
        SqlStore::fromPdo(new \PDO('sqlite::memory:')),
        'non-default'
    );
    ```

    Note: when using ```SqlStore::fromPdo``` the pdo error mode will be set.  It will default to exception such that
    a test that triggers an error will throw an exception and fail.  This default behavior can be overridden by
    passing an error mode as the second parameter of the factory method.

2. Define Blueprints

    Blueprints are what define the entities in your data store by allowing you to configure the following:
    * The `StoreInterface` to use when saving or retrieving data for the blueprint
    * The table/collection in which the data will be stored
    * The default values for columns/properties in the table/collection.  Default values allow you to only
deal with the data that is truly important to your test logic and not waste time and clutter your test code
with setting values that are meaningless but required by your data store
    * The relationships between the blueprint and other previously defined blueprints
    
    Multiple blueprints may be defined for the same table/collection.  This allows you to quickly set up
use multiple sets of default values for your data.  The below example shows the creation of two blueprints
for a user table with different roles.  Defining multiple blueprints makes your tests more readable not to
mention faster to write.

    ```php
    <?php
    
    use DerpTest\Machinist\Machinist;
    use DerpTest\Machinist\Store\SqlStore;
    use DerpTest\Machinist\Blueprint;
    
    // Register the default data store
    Machinist::store(SqlStore::fromPdo(new \PDO('sqlite::memory:')));
    
    // Create a company blueprint using the "hard way".  This will be used in a relationship
    $company = new Blueprint(
        Machinist::instance(),
        'company',
        array(
            'streetAddress' => '123 Any Street',
            'city'          => 'Any Town',
            'state'         => 'NV',
            'zip'           => '89101'
        )
    );
    Machinist::instance()->addBlueprint('company', $company);
    
    // Create a standard user blueprint using the "easy way"
    Machinist::blueprint(
        'user',                                            // This is the blueprint name
        array(
            'role'    => 'USER',                           // The user will default to the STANDARD_USER role
            'active'  => true                              // The user will default to active
            'company' => Machinist::relationship($company) // Create relationship
        ),
        'user',                                            // The "user" table/collection to used.  Not required
                                                           // as the name is the same as the table/collection
        'default'                                          // Data store.  Not required if "default"
    );
    
    // Create an administrator user blueprint using the "easy way"
    Machinist::blueprint(
        'administratorUser',                               // This is the blueprint name
        array(
            'role'    => 'ADMINISTRATOR',                  // The user will default to the STANDARD_USER role
            'active'  => true                              // The user will default to active
            'company' => Machinist::relationship($company) // Create relationship with company blueprint
        ),
        'user'                                             // The "user" table/collection to used.  Required as
                                                           // the blueprint name is not the same as the
                                                           // table/collection
    );
        
    ```

## Relationships in depth
Relationships are quite possibly the strongest feature of PHP Machinist.  They allow you to quickly associate
related data without having to worry about primary keys, foreign keys, and all that other nonsense in your actual tests.
They will even do some _"find or create"_ magic for you.  Here is how you use the Blueprints from the example above to
create two users and one company really quickly.

```php
<?php
// ...
 
Machinist::blueprint('user')->make(
    array(
        'username' => 'pedro@voteforpedro.org',
        'company' => array('name' => 'Pedro for Class President')
    )
);


Machinist::blueprint('user')->make(
    array(
        'username' => 'napoleon@voteforpedro.org',
        'company' => array('name' => 'Pedro for Class President')
    )
);

```

That's the fill sum of the code needed to create two users and one company.  PHP Machinist did some magic on the first
blueprint make call.  It looked for a company with the `name` of `Pedro for Class President`.  It didn't find one, so
it created a company and used that for the relationship.  For the second `make()` call, it found the company created in
the first call and used that company for the relationship.

Relationships will also populate data from data finds as well.  Here is an example using the same blueprints and the
data created from the previous example.

```php
<?php
// ...
 
$pedro = Machinist::blueprint('user')->findOne(array('username' => 'pedro@voteforpedro.org'));
echo ($pedro->company->name);
```

This will result in `Pedro for Class President` being shown on the screen.

## License
See LICENSE file in the project

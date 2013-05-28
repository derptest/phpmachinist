[![Build Status](https://travis-ci.org/derptest/phpmachinist.png?branch=master)](https://travis-ci.org/derptest/phpmachinist)
# PHPMachinist: Testing Object Factory

## What???!??!
A Slightly less annoying way of creating database fixtures for PHP testing. It borrows heavily from
several projects:

* https://github.com/chriskite/phactory
* https://github.com/thoughtbot/factory_girl
* https://github.com/ccollins/milkman
* https://github.com/notahat/machinist

They're totally awesome. Just either in the wrong language, or didn't quite do what I wanted.

## Install
Add the package derptest/phpmachinist to your composer.json
For more information about Composer, please visit http://getcomposer.org

## Configure
Configuration of PHP Machinist happens in two steps:

1. Register data stores

    Registering data stores is done via either the static `Machinist::Store()` method or the
`addStore()` method on a Machinist instance.  Both methods take the same parameters, a `Store`
instance and an optional name for that store.  If no name is given, it will default to `default`.
Below is an example of both:

    ```php
    <?php
    use DerpTest\Machinist\Machinist;
    use DerpTest\Machinist\Store\SqlStore;
    
    // This store will be referenced by the name "default"
    Machinist::Store(SqlStore::fromPdo(new \PDO('sqlite::memory:'));
    
    // This store will be referenced by the name "non-default"
    Machinist::instance()->addStore(
        SqlStore::fromPdo(new \PDO('sqlite::memory:'),
        'non-default'
    );
    ```

2. Define Blueprints

    Blueprints are what define the entities in your data store by allowing you to configure the following:
    * The `Store` to use when saving or retrieving data for the blueprint
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
    Machinist::Store(SqlStore::fromPdo(new \PDO('sqlite::memory:'));
    
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
    Machnist::Blueprint(
        'user',                                            // This is the blueprint name
        array(
            'role'    => 'USER',                           // The user will default to the STANDARD_USER role
            'active'  => true                              // The user will default to active
            'company' => Machinist::Relationship($company) // Create relationshi
        ),
        'user',                                            // The "user" table/collection to used
        'default'                                          // Data store.  Not required if "default"
    );
    
    // Create an administrator user blueprint using the "easy way"
    Machnist::Blueprint(
        'administratorUser',                               // This is the blueprint name
        array(
            'role'    => 'ADMINISTRATOR',                  // The user will default to the STANDARD_USER role
            'active'  => true                              // The user will default to active
            'company' => Machinist::Relationship($company) // Create relationship with company blueprint
        ),
        'user'                                             // The "user" table/collection to used
    );
        
    ```
    

## Examply thing
some tables:

	create table `stuff` ( 
		`id` INTEGER PRIMARY KEY AUTOINCREMENT, 
		`name` varchar(100), 
		`box_id` INTEGER NULL DEFAULT NULL
    );
    create table `box` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `type` varchar(100)
    );


	use machinist\Machinist;
	use machinist\driver\SqlStore;
	// setup a default connection to use
	Machinist::Store(SqlStore::fromPdo(new \PDO('sqlite::memory:'));
	// make a blueprint for cardboardb oxes.. in the box table.. with a type..
	$boxBlueprint = Machinist::Blueprint("cardboardbox", "box", array("type" => "cardboard"));

	Machinist::Blueprint("crayon",
	    array(
	        "name" => "crayon",
	        "box" => Machinist::Relationship($boxBlueprint)->local("box_id"),
	    ),
	    "stuff");

	$crayon = Machinist::Blueprint("crayon")->make();
    $redCrayon = Machinist::Blueprint("crayon")->make(array("name" => "red crayon"));

## Testing
Testing of the source code can be done with the PHPUnit version 3.6 or better.

To begin testing you must first add the dependencies by performing a Composer install with the --dev parameter  This will place the all of the dependencies in the vendor directory.  For more information on performing a Composer install, please visit http://getcomposer.org/doc/00-intro.md#installation

A default phpunit.xml.dist is configured in the test directory.  You can create your own phpunit.xml and it will be ignored by git.

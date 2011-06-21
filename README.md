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
http://pearhub.org/projects/machinist

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


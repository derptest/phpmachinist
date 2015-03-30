Change Log for PHP Machinist
============================

3.2.0
=====

* Update SQLStore definition queries to use prepare/exec instead of query form HHVM compatibility

3.1.0
=====

* Set error mode for PDO store and default to exceptions
* Update doctrine store to wipe with delete to resolve compatibility issues with older Doctrine versions
* Update development dependencies for PHPUnit and Phake

3.0.0
=====

* Adhere to PSR-1 code standard

2.1.0
=====

* More namespacing fixes
* Remove unused PK get form Blueprint::find()
* Remove global functions as they are no longer needed

2.0.0
-----

* Move namespacing to Derp Test namespace
* Rename driver to store in namespace to properly represent the object name
* Add count() method to blueprints
* Fix syntax errors in documentation
* Add initial MongoDB integration

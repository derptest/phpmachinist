<?php
namespace machinist\behat\functions;
use \machinist\Machinist;
use \machinist\driver\SqlStore;
use \assertEquals;
use \stdClass;

class SweetTestTable {
	public function __construct($hash) {
		$this->hash = $hash;
	}
	public function getHash() {
		return $this->hash;
	}
}
class FunctionTest extends \PHPUnit_Framework_TestCase {
	private $pdo;

	public function setup() {
		$this->pdo = new \PDO("sqlite::memory:");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('DROP TABLE IF EXISTS `box`');
		$this->pdo->exec('DROP TABLE IF EXISTS `stuff`');
		$this->pdo->exec('create table `box` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `other_field` varchar(100));');
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `box_id` INTEGER NULL DEFAULT NULL);');
		\machinist\Machinist::reset();
		\machinist\Machinist::Store(SqlStore::fromPdo($this->pdo));
	}

	public static function relationalOverrideProvider() {
		return array(
			array("column: this is my value", array('column' => 'this is my value')),
			array("col1: this is my value, col2: val2", array('col1' => 'this is my value', 'col2' => 'val2'))
		);
	}
	/**
	 * @dataProvider relationalOverrideProvider
	 */
	public function testRelationalOverridesWithSingleValue($value, $expected) {
		$actual = findRelationalOverrides($value);
		assertEquals($expected, $actual);
	}

	public function testCreateMachinesFromTable() {
		$box_bp = Machinist::Blueprint("box", array(
			'name' => "My Box"
		));
		$stuff_bp = Machinist::Blueprint("stuff", array(
			'name' => "My Stuff",
			'Box' => Machinist::Relationship($box_bp)->local('box_id')
		));

		$sweet_box = $box_bp->make(array('name' => 'amazing'));

		$world = new stdClass();

		$blueprint = "stuff";
		$table = new SweetTestTable(array(array('name' => 'sweet', "Box" => "name: amazing")));
		createMachinesFromTable($world, $blueprint, $table);
		assertEquals(1, count($world->stuff));
		assertEquals("amazing", $world->stuff[0]->Box->name);
		assertEquals("sweet", $world->stuff[0]->name);
	}

	public function testCreateMachinesFromTableWith2Fields() {
		$box_bp = Machinist::Blueprint("box", array(
			'name' => "My Box",
			'other_field' => 'My Other Field'
		));
		$stuff_bp = Machinist::Blueprint("stuff", array(
			'name' => "My Stuff",
			'Box' => Machinist::Relationship($box_bp)->local('box_id')
		));

		$sweet_box = $box_bp->make(array('name' => 'amazing'));

		$world = new stdClass();

		$blueprint = "stuff";
		$table = new SweetTestTable(array(array('name' => 'sweet', "Box" => "name: amazing, other_field: awesome")));
		createMachinesFromTable($world, $blueprint, $table);
		assertEquals(1, count($world->stuff));
		assertEquals("amazing", $world->stuff[0]->Box->name);
		assertEquals("awesome", $world->stuff[0]->Box->other_field);
		assertEquals("sweet", $world->stuff[0]->name);
	}
}

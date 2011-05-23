<?php
use \machinist\Machinist;
use \machinist\driver\SqlStore;

class IntegrationTest extends PHPUnit_Framework_TestCase {
	private $pdo;
	public function setUp() {
	    $this->pdo = new PDO("sqlite::memory:");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `box_id` INTEGER NULL DEFAULT NULL);');
		$this->pdo->exec('create table `box` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100) );');

		\machinist\Machinist::Store(SqlStore::fromPdo($this->pdo));

	}

	public function tearDown() {
		\machinist\Machinist::reset();
		+$this->pdo->exec('DROP TABLE `stuff`;');

	}

	public function testWeCanCreateOneStuff() {
		$bp_row = Machinist::Blueprint("test", "stuff", array('name' => "hello"))
			->make();
		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals("hello", $row->name);
	}
	public function testReturnedDataHasPrimaryKeyCorrect() {
		$bp_row = Machinist::Blueprint("test", "stuff", array('name' => "hello"))
			->make();
		$this->assertEquals(1, $bp_row->id);
	}

	public function testOverridesOverride() {
		$bp = Machinist::Blueprint("test", "stuff", array('name' => "hello"));

		$row = Machinist::Blueprint("test")->make(array('name' => "something else"));
		$this->assertEquals("something else", $row->name);

		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals("something else", $row->name);
	}

	public function testOverrideCallable() {
				$bp = Machinist::Blueprint("test", "stuff", array('name' => "hello"));

		$row = Machinist::Blueprint("test")->make(array('name' => function($d) { return "something else"; }));
		$this->assertEquals("something else", $row->name);

		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals("something else", $row->name);
	}

	public function testForeignKeyThing() {
		$box = Machinist::Blueprint("box", "box", array('name' => 'square box'));

		Machinist::Blueprint("stuff_in_a_box", "stuff", array(
			'name' => "hello",
			'box' => Machinist::Relationship($box)->local('box_id')
		));

		$bp_row = Machinist::Blueprint('stuff_in_a_box')->make();


		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals(1, $row->box_id);
		$this->assertEquals(1, $bp_row->box->id);
		$this->assertEquals("square box", $bp_row->box->name);

	}

	public function testForeignKeyOverride() {
		$box = Machinist::Blueprint("box", "box", array('name' => 'square box'));

		Machinist::Blueprint("stuff_in_a_box", "stuff", array(
			'name' => "hello",
			'box' => Machinist::Relationship($box)->local('box_id')
		));

		$bp_row = Machinist::Blueprint('stuff_in_a_box')->make(
			array('box' => array('name' => 'round box'))
		);

		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals(1, $row->box_id);
		$this->assertEquals(1, $bp_row->box->id);
		$this->assertEquals("round box", $bp_row->box->name);

	}

		public function testDefaultsTbaleToName() {
		$bp_row = Machinist::Blueprint("stuff", array('name' => "whoa it might work"))
			->make();
		$query = $this->pdo->prepare('SELECT * from stuff where id = 1');
		$query->execute(array());
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals("whoa it might work", $row->name);
	}
}

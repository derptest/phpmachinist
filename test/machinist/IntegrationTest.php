<?php
use \machinist\Machinist;
use \machinist\driver\SqlStore;

class IntegrationTest extends PHPUnit_Framework_TestCase {
	private $pdo;
	public function setUp() {
		if (file_exists('test.db')) {
			unlink('test.db');
		}
	    $this->pdo = new PDO("sqlite:test.db");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100) );');

		\machinist\Machinist::Store(SqlStore::fromPdo($this->pdo));

	}

	public function tearDown() {
		\machinist\Machinist::reset();
		$this->pdo->exec('DROP TABLE `stuff`;');
		if (file_exists('test.db')) {
			unlink('test.db');
		}
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
}

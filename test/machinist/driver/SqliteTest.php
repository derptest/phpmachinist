<?php
use \machinist\driver\SqlStore;
use \machinist\driver\Sqlite;
class SqliteTest extends PHPUnit_Framework_TestCase {
	private $driver;
	private $pdo;

	public function setUp() {
		if (file_exists('test.db')) {
			unlink('test.db');
		}
	    $this->pdo = new PDO("sqlite::memory:");
//		$this->pdo = new PDO("sqlite:test.db");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100) );');
		$this->pdo->exec('DROP TABLE IF EXISTS `some_stuff`;');
		$this->pdo->exec('CREATE TABLE `some_stuff` (
  			`some_id` int(10)  NOT NULL,
  			`stuff_id` int(10)  NOT NULL,
  			`name` VARCHAR(100),
			PRIMARY KEY (`some_id`,`stuff_id`));');
		$this->driver = SqlStore::fromPdo($this->pdo);
	}

	public function tearDown() {
		$this->pdo->exec('DROP TABLE `stuff`;');
		unset($this->pdo);
	}

	public function testSqlStoreGetsInstance() {
		$this->assertInstanceOf('\machinist\driver\Sqlite', SqlStore::fromPdo($this->pdo));
	}

	public function testGetPrimaryKey() {
		$this->assertEquals('id', $this->driver->primaryKey('stuff'));
	}

	public function testInsertReturnsKey() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$this->assertEquals(1, $id);
	}

	public function testInsertStoresCorrectValue() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$query = $this->pdo->prepare('SELECT * from stuff where id = ?');
		$query->execute(array($id));
		$row = $query->fetch(PDO::FETCH_OBJ);
		$this->assertEquals("stupid", $row->name);
	}

	public function testFindPullsRow() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$row = $this->driver->find('stuff', $id);
		$this->assertEquals("stupid", $row->name);
	}

	public function testEmptyNoTruncateDeletesRows() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$row = $this->driver->find('stuff', $id);
		$this->assertNotEmpty($row);
		$this->driver->wipe('stuff', false);
		$row = $this->driver->find('stuff', $id);
		$this->assertEmpty($row);
	}

	public function testEmptyTruncateDeletesRows() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$row = $this->driver->find('stuff', $id);
		$this->assertNotEmpty($row);
		$this->driver->wipe('stuff', true);
		$row = $this->driver->find('stuff', $id);
		$this->assertEmpty($row);
	}

	public function testLocatesByColumn() {
		$id = $this->driver->insert('stuff', array('name' => 'stupid'));
		$row = $this->driver->find('stuff', array('name' => 'stupid'));
		$this->assertNotEmpty($row);
		$this->assertEquals($row[0]->id, $id);
	}


	public function testFindCompoundPrimareyKey() {
		$ids = $this->driver->primaryKey('some_stuff');
		$this->assertEquals(array('some_id', 'stuff_id'), $ids);
	}
}

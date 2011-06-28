<?php
use \machinist\driver\SqlStore;
use \machinist\driver\Mysql;

class MysqlTest extends PHPUnit_Framework_TestCase {
	private $driver;
	private $pdo;
	public function setUp() {
		$this->pdo = new PDO('mysql:host=localhost;dbname=machinist_test', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$this->pdo->exec('DROP TABLE IF EXISTS `stuff`;');
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTO_INCREMENT, `name` varchar(100) );');
		$this->pdo->exec('DROP TABLE IF EXISTS `some_stuff`;');
		$this->pdo->exec('CREATE TABLE `some_stuff` (
`some_id` int(10) unsigned NOT NULL,
`stuff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`name` VARCHAR(100),
PRIMARY KEY (`some_id`,`stuff_id`));');
		$this->driver = SqlStore::fromPdo($this->pdo);
	}

	public function tearDown() {
		$this->pdo->exec('DROP TABLE `stuff`;');
		$this->pdo->exec('DROP TABLE `some_stuff`;');
	}

	public function testSqlStoreGetsInstance() {
		$this->assertInstanceOf('\machinist\driver\Mysql', SqlStore::fromPdo($this->pdo));
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

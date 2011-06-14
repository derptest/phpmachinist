<?php
use \machinist\driver\SqlStore;
use \machinist\driver\Mysql;

class MysqlTest extends PHPUnit_Framework_TestCase {
	private $driver;
	private $pdo;
	public function setUp() {
		$this->pdo = new PDO('mysql:host=localhost;dbname=machinist_test', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTO_INCREMENT, `name` varchar(100) );');
		$this->driver = SqlStore::fromPdo($this->pdo);
	}

	public function tearDown() {
		$this->pdo->exec('DROP TABLE `stuff`;');
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
}

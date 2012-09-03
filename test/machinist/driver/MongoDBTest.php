<?php
namespace machinist\driver;

use Mongo, MongoDB, MongoDBRef;
use machinist\driver\MongoDB as MongoDBDriver;

/**
 * Unit test for the MongoDB driver
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 */
class MongoDBTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Mongo
	 */
	private $mongo;

	/**
	 * @var MongoDB
	 */
	private $mongo_db;

	/**
	 * @var MongoDBDriver
	 */
	private $driver;

	protected function setUp() {
		parent::setUp();
		$this->mongo = new Mongo($_ENV['DoctrineMongoDB_DSN']);
		$this->mongo_db = $this->mongo->selectDB($_ENV['DoctrineMongoDB_DB']);
		$this->driver = new MongoDBDriver($this->mongo_db);
		foreach ($this->mongo_db->listCollections() as $collection) {
			$this->mongo_db->dropCollection($collection);
		}
	}

	protected function tearDown() {
		unset($this->driver);
		unset($this->mongo_db);
		unset($this->mongo);
	}

	public function testGetPrimaryKey() {
		$pk = new \stdClass();
		$pk->foo = "bar";
		$this->mongo_db->Stuff->insert($pk);
		$this->assertEquals('_id', $this->driver->primaryKey('Stuff'));
	}

	public function testInsertReturnsKey() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$collection = $this->mongo_db->Stuff->find(array('name' => 'stupid'));
		$this->assertNotNull($collection, "unable to locate record in DB");
		$this->assertEquals(1, $collection->count(), 'More than on item found');
		$inserted = $collection->getNext();
		$expected = $inserted['_id'];
		$this->assertEquals($expected, $id);
	}

	public function testInsertStoresCorrectValue() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$stuff = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertEquals('stupid', $stuff['name']);
	}

	public function testInsertStoresCorrectValueMongoID() {
		$id = new \MongoId();
		$this->driver->insert('Stuff', array('_id' => $id, 'name' => 'stupid'));
		$stuff = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertEquals('stupid', $stuff['name']);
	}

	public function testInsertStoresCorrectValueMongoDate() {
		$date = new \MongoTimestamp();
		$id = $this->driver->insert('Stuff', array('date' => $date));
		$stuff = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertEquals($date, $stuff['date']);
	}

	public function testFindPullsRow() {
		$stuff = array('name' => 'stupid');
		$this->mongo_db->Stuff->insert($stuff);
		$id = $stuff['_id'];
		$rows = $this->driver->find('Stuff', $id);
		$this->assertEquals(1, count($rows),
						'Unexpected number of records returned');
		$row = array_pop($rows);
		$this->assertEquals("stupid", $row['name']);
	}

	public function testEmptyNoTruncateDeletesRows() {
		$stuff = array('name' => 'stupid');
		$this->mongo_db->Stuff->insert($stuff);
		$id = $stuff['_id'];
		$row = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertNotEmpty($row);
		$this->driver->wipe('Stuff', false);
		$row = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertEmpty($row);
	}

	public function testEmptyTruncateDeletesRows() {
		$stuff = array('name' => 'stupid');
		$this->mongo_db->Stuff->insert($stuff);
		$id = $stuff['_id'];
		$row = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertNotEmpty($row);
		$this->driver->wipe('Stuff', true);
		$row = $this->mongo_db->Stuff->findOne(array('_id' => $id));
		$this->assertEmpty($row);
	}

	public function testLocatesByColumn() {
		$stuff = array('name' => 'stupid');
		$this->mongo_db->Stuff->insert($stuff);
		$id = $stuff['_id'];
		$rows = $this->driver->find('Stuff', array('name' => 'stupid'));
		$this->assertNotEmpty($rows);
		$row = array_pop($rows);
		$this->assertEquals($row['_id'], $id);
	}

	public function testMultiLevelDocument() {
		$stuff = array(
				'name' => 'stupid',
				'thestuff' => array(
						'blue',
						'green',
						'yellow'
				));
		$this->mongo_db->Stuff->insert($stuff);
		$id = $stuff['_id'];
		$rows = $this->driver->find('Stuff', $id);
		$this->assertNotEmpty($rows);
		$row = array_pop($rows);
		$this->assertEquals($row['_id'], $id);
		$this->assertEquals('stupid', $row['name']);
		$this->assertInternalType('array', $row['thestuff']);
		$this->assertEquals(array('blue','green','yellow'), $row['thestuff']);
	}

	public function testDBRefenceReturnsId() {
		$other_stuff = array('foo' => 'bar');
		$this->mongo_db->OtherStuff->insert($other_stuff);
		$stuff = array(
				'name' => 'stupid',
				'other_stuff' => MongoDBRef::create('OtherStuff', $other_stuff['_id']));
		$this->mongo_db->Stuff->insert($stuff);
		$found = $this->driver->find('Stuff', $stuff['_id']);
		$found_stuff = array_pop($found);
		$this->assertEquals($other_stuff['_id'], $found_stuff['other_stuff']);
	}
}
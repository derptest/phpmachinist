<?php
namespace machinist\driver;

use Mongo, MongoDB, MongoDBRef;
use machinist\driver\MongoDB as MongoDBDriver;
use machinist\Machinist as m;
use machinist\relationship\Relationship as r;
use machinist\Blueprint as b;

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

	/**
	 * @var \machinist\Machinist 
	 */
	private $machinist;

	protected function setUp() {
		parent::setUp();
		$this->mongo = new Mongo($_ENV['DoctrineMongoDB_DSN']);
		$this->mongo_db = $this->mongo->selectDB($_ENV['DoctrineMongoDB_DB']);
		$this->driver = new MongoDBDriver($this->mongo_db);
		foreach ($this->mongo_db->listCollections() as $collection) {
			$this->mongo_db->dropCollection($collection);
		}
		$this->machinist = new \machinist\Machinist();
		$this->machinist->addStore($this->driver, 'default');
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
		$row = $this->driver->find('Stuff', $id);
		$this->assertEquals("stupid", $row->name);
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
		$this->assertEquals($row->_id, $id);
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
		$row = $this->driver->find('Stuff', $id);
		$this->assertEquals($row->_id, $id);
		$this->assertEquals('stupid', $row->name);
		$this->assertInternalType('array', $row->thestuff);
		$this->assertEquals(array('blue','green','yellow'), $row->thestuff);
	}

	public function testDBRefenceReturnsMongoDBRef() {
		$other_stuff = array('foo' => 'bar');
		$this->mongo_db->OtherStuff->insert($other_stuff);
		$stuff = array(
				'name' => 'stupid',
				'other_stuff' => MongoDBRef::create('OtherStuff', $other_stuff['_id']));
		$this->mongo_db->Stuff->insert($stuff);
		$found_stuff = $this->driver->find('Stuff', $stuff['_id']);
		$this->assertTrue(\MongoDBRef::isRef($found_stuff->other_stuff));
		$this->assertEquals($other_stuff['_id'], $found_stuff->other_stuff['$id']);
	}
	
	public function testBluePrintMongoDate() {
		$date = new \MongoDate(strtotime('yesterday'));
		$other_date = new \MongoDate();
		$bp = new \machinist\Blueprint(
						$this->machinist,
						'Stuff',
						array('date' => $date));
		$bp = $bp->make(array('otherDate' => $other_date));
		$actual = $this->mongo_db->Stuff->findOne(array('_id' => $bp->_id));
		$this->assertEquals($date, $actual['date']);
		$this->assertEquals($other_date, $actual['otherDate']);
	}
	
	public function testBluePrintMongoTimestamp() {
		$date = new \MongoTimestamp(strtotime('yesterday'));
		$other_date = new \MongoTimestamp();
		$bp = new \machinist\Blueprint(
						$this->machinist,
						'Stuff',
						array('date' => $date));
		$bp = $bp->make(array('otherDate' => $other_date));
		$actual = $this->mongo_db->Stuff->findOne(array('_id' => $bp->_id));
		$this->assertEquals($date, $actual['date']);
		$this->assertEquals($other_date, $actual['otherDate']);
	}

	public function testRelationshipPersistsCorrectly() {
		$other_stuff_bp = new \machinist\Blueprint(
						$this->machinist,
						'OtherStuff',
						array('name' => 'other_stuff'));
		$relationship = new r($other_stuff_bp);
		$relationship->local('other_stuff_id');
		$stuff_bp = new b(
						$this->machinist,
						'Stuff',
						array(
								'name' => 'stuff',
								'other_stuff' => $relationship
						));
		$stuff_machine = $stuff_bp->make();
		$stuff_id = $stuff_machine->_id;
		$actual_stuff = $this->mongo_db->Stuff->findOne(array('_id' => $stuff_id));
		$this->assertEquals('stuff', $actual_stuff['name']);
		$this->assertInstanceOf('\MongoId', $actual_stuff['other_stuff_id']);
		$actual_other_stuff = $this->mongo_db->OtherStuff->findOne(array('_id' => $actual_stuff['other_stuff_id']));
		$this->assertEquals('other_stuff', $actual_other_stuff['name']);
	}

	public function testRelationshipFindsCorrectly() {
		$other_stuff = array('name' => 'other stuff');
		$this->mongo_db->OtherStuff->insert($other_stuff);
		$stuff = array('name' => 'stuff', 'other_stuff_id' => $other_stuff['_id']);
		$this->mongo_db->Stuff->insert($stuff);
		
		$other_stuff_bp = new b($this->machinist, 'OtherStuff');
		$relationship = new r($other_stuff_bp);
		$relationship->local('other_stuff_id');

		$stuff_bp = new b(
						$this->machinist,
						'Stuff',
						array(
								'name' => 'stuff',
								'other_stuff' => $relationship
						));
	
		$machine = $stuff_bp->findOne($stuff['_id']);
		$this->assertEquals('stuff', $machine->name);
		$this->assertEquals('other stuff', $machine->other_stuff->name);
	}
}
<?php
namespace machinist\driver;

require_once 'testentity/doctrine/Stuff.php';

use testentity\doctrine\Group;
use testentity\doctrine\Stuff;
use testentity\doctrine\SomeStuff;

/**
 * Description of DoctrineTest
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 */
class DoctrineTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var Doctrine
	 */
	private $driver;

	protected function setUp() {
		parent::setUp();
		// Build an Entity Manager
		$pdo = new \PDO($_ENV['Doctrine_Driver_SQLite_DSN'],
				null, null, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		$dbal_driver = new \Doctrine\DBAL\Driver\PDOSqlite\Driver();
		$conn = new \Doctrine\DBAL\Connection(array('pdo' => $pdo), $dbal_driver);

		$orm_config = new \Doctrine\ORM\Configuration();
		$orm_config->setAutoGenerateProxyClasses(true);
		$orm_config->setMetadataDriverImpl(
						$orm_config->newDefaultAnnotationDriver(__DIR__ . '/testentity/doctrine'));
		$orm_config->setProxyDir($_ENV['Doctrine_Driver_Proxy_Root_Directory']);
		$orm_config->setProxyNamespace('machinist\driver\testentity\doctrine\proxy');
		$this->em = \Doctrine\ORM\EntityManager::create($conn, $orm_config, null);

		// Build the schema for testing
		$schema_tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
		$metadatas = $this->em->getMetadataFactory()->getAllMetadata();
		$schema_tool->dropDatabase();
		$schema_tool->createSchema($metadatas);
	
		$this->driver = new Doctrine($this->em,
						array('machinist\\driver\\testentity\\doctrine'));
	}

	protected function tearDown() {
		unset($this->em);
	}

	public function testGetPrimaryKey() {
		$this->assertEquals('id', $this->driver->primaryKey('Stuff'));
	}

	/**
	 * @expectedException \InvalidArgumentException 
	 */
	public function testEntityCantResolveThrowsException() {
		$this->driver->primaryKey('Bunk');
	}

	public function testEntityNoResolveRequired() {
		$driver = new Doctrine($this->em);
		$id = $driver->primaryKey('machinist\\driver\\testentity\\doctrine\\Stuff');
		$this->assertEquals('id', $id);
	}

	public function testEntityResolveOneOfManyNamespace() {
		$driver = new Doctrine($this->em, array(
				'unresolvable\\one',
				'machinist\\driver\\testentity\\doctrine',
				'unresolvable\\two'
		));
		$this->assertEquals('id', $this->driver->primaryKey('Stuff'));
	}

	public function testColumns() {
		$cols = $this->driver->columns('Stuff');
		$this->assertEquals(array('id', 'name'), $cols);
	}

	public function testInsertReturnsKey() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$this->assertEquals(1, $id);
	}

	public function testInsertStoresCorrectValue() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$entity_name = 'machinist\driver\testentity\doctrine\Stuff';
		$stuff = $this->em->find($entity_name, $id);
		$this->assertEquals('stupid', $stuff->getName());
	}

	public function testFindPullsRow() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$row = $this->driver->find('Stuff', $id);
		$this->assertEquals("stupid", $row->name);
	}

	public function testEmptyNoTruncateDeletesRows() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$row = $this->driver->find('Stuff', $id);
		$this->assertNotEmpty($row);
		$this->driver->wipe('Stuff', false);
		$row = $this->driver->find('Stuff', $id);
		$this->assertEmpty($row);
	}

	public function testEmptyTruncateDeletesRows() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$row = $this->driver->find('Stuff', $id);
		$this->assertNotEmpty($row);
		$this->driver->wipe('Stuff', true);
		$row = $this->driver->find('Stuff', $id);
		$this->assertEmpty($row);
	}

	public function testLocatesByColumn() {
		$id = $this->driver->insert('Stuff', array('name' => 'stupid'));
		$row = $this->driver->find('Stuff', array('name' => 'stupid'));
		$this->assertNotEmpty($row);
		$this->assertEquals($row[0]->id, $id);
	}

	public function testFindCompoundPrimareyKey() {
		$ids = $this->driver->primaryKey('SomeStuff');
		$this->assertEquals(array('some_id', 'stuff_id'), $ids);
	}

	public function testInsertingIntoGroup() {
		$what = $this->driver->insert('Group', array('name' => "Hello"));
		$found = $this->driver->find('group', array('id' => $what));
		$this->assertEquals("Hello", $found[0]->name);
	}
}
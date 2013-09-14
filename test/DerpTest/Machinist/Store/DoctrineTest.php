<?php
namespace DerpTest\Machinist\Store;

use DerpTest\Machinist\Store\TestEntity\Doctrine\Master;
use DerpTest\Machinist\Store\TestEntity\Doctrine\Detail;
use DerpTest\Machinist\Store\TestEntity\Doctrine\ManyLeft;
use DerpTest\Machinist\Store\TestEntity\Doctrine\ManyRight;

/**
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

    protected function setUp()
    {
        parent::setUp();
        // Build an Entity Manager
        $pdo = new \PDO($_ENV['Doctrine_Store_SQLite_DSN'],
            null, null, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        $dbal_driver = new \Doctrine\DBAL\Driver\PDOSqlite\Driver();
        $conn = new \Doctrine\DBAL\Connection(array('pdo' => $pdo), $dbal_driver);

        $orm_config = new \Doctrine\ORM\Configuration();
        $orm_config->setAutoGenerateProxyClasses(true);
        $orm_config->setMetadataDriverImpl(
            $orm_config->newDefaultAnnotationDriver(__DIR__ . '/TestEntity/Doctrine'));
        $orm_config->setProxyDir($_ENV['Doctrine_Store_Proxy_Root_Directory']);
        $orm_config->setProxyNamespace('DerpTest\Machinist\Store\TestEntity\Doctrine\proxy');
        $this->em = \Doctrine\ORM\EntityManager::create($conn, $orm_config, null);

        // Build the schema for testing
        $schema_tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $schema_tool->dropDatabase();
        $schema_tool->createSchema($metadatas);

        $this->driver = new Doctrine($this->em,
            array('DerpTest\Machinist\\Store\\TestEntity\\Doctrine'));
    }

    protected function tearDown()
    {
        unset($this->em);
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', $this->driver->primaryKey('Stuff'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEntityCantResolveThrowsException()
    {
        $this->driver->primaryKey('Bunk');
    }

    public function testEntityNoResolveRequired()
    {
        $driver = new Doctrine($this->em);
        $id = $driver->primaryKey('DerpTest\Machinist\\Store\\TestEntity\\Doctrine\\Stuff');
        $this->assertEquals('id', $id);
    }

    public function testEntityResolveOneOfManyNamespace()
    {
        $driver = new Doctrine($this->em, array(
            'unresolvable\\one',
            'DerpTest\Machinist\\Store\\TestEntity\\Doctrine',
            'unresolvable\\two'
        ));
        $this->assertEquals('id', $this->driver->primaryKey('Stuff'));
    }

    public function testInsertReturnsKey()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $this->assertEquals(1, $id);
    }

    public function testInsertStoresCorrectValue()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $entity_name = 'DerpTest\Machinist\Store\TestEntity\Doctrine\Stuff';
        $stuff = $this->em->find($entity_name, $id);
        $this->assertEquals('stupid', $stuff->getName());
    }

    public function testFindPullsRow()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $row = $this->driver->find('Stuff', $id);
        $this->assertEquals("stupid", $row->name);
    }

    public function testEmptyNoTruncateDeletesRows()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $row = $this->driver->find('Stuff', $id);
        $this->assertNotEmpty($row);
        $this->driver->wipe('Stuff', false);
        $row = $this->driver->find('Stuff', $id);
        $this->assertEmpty($row);
    }

    public function testEmptyTruncateDeletesRows()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $row = $this->driver->find('Stuff', $id);
        $this->assertNotEmpty($row);
        $this->driver->wipe('Stuff', true);
        $row = $this->driver->find('Stuff', $id);
        $this->assertEmpty($row);
    }

    public function testLocatesByColumn()
    {
        $id = $this->driver->insert('Stuff', array('name' => 'stupid'));
        $row = $this->driver->find('Stuff', array('name' => 'stupid'));
        $this->assertNotEmpty($row);
        $this->assertEquals($row[0]->id, $id);
    }

    public function testFindCompoundPrimareyKey()
    {
        $ids = $this->driver->primaryKey('SomeStuff');
        $this->assertEquals(array('some_id', 'stuff_id'), $ids);
    }

    public function testInsertingIntoGroup()
    {
        $what = $this->driver->insert('Group', array('name' => "Hello"));
        $found = $this->driver->find('group', array('id' => $what));
        $this->assertEquals("Hello", $found[0]->name);
    }

    public function testManyToOne()
    {
        $detail = new Detail();
        $detail->setName('Detail Name');
        $master = new Master();
        $master->setDetail($detail);
        $this->em->persist($master);
        $this->em->persist($detail);
        $this->em->flush();
        $machine = $this->driver->find('master', $master->getId());
        $this->assertEquals('Detail Name', $machine->detail->name);
        $this->assertEquals($detail->getId(), $machine->detail_id);
    }

    public function testManyToMany()
    {
        $left1 = new ManyLeft();
        $left2 = new ManyLeft();
        $right1 = new ManyRight();
        $right2 = new ManyRight();
        $left1->setManyRights(array($right1, $right2));
        $left2->setManyRights(array($right1));
        $right1->setManyLefts(array($left1, $left2));
        $right2->setManyLefts(array($left1));
        $this->em->persist($left1);
        $this->em->persist($left2);
        $this->em->persist($right1);
        $this->em->persist($right2);
        $this->em->flush();
        $machine = $this->driver->find('ManyLeft', $left1->getId());
        $this->assertEquals(2, count($machine->rights),
            'Unexpected number of rights in left1');
        foreach ($machine->rights as $right) {
            $this->assertContains($right->id,
                array($right1->getId(), $right2->getId()),
                'Unexpected value for ID in right for left1');
        }
        $machine = $this->driver->find('ManyLeft', $left2->getId());
        $this->assertEquals(1, count($machine->rights),
            'Unexpected number of rights in left2');
        foreach ($machine->rights as $right) {
            $this->assertEquals($right->id, $right1->getId(),
                'Unexpected value for ID in right for left2');
        }
        $machine = $this->driver->find('ManyRight', $right1->getId());
        $this->assertEquals(2, count($machine->lefts),
            'Unexpected number of lefts right1');
        foreach ($machine->lefts as $left) {
            $this->assertContains($left->id,
                array($left1->getId(), $left2->getId()),
                'Unexpected value for ID in left for right1');
        }
        $machine = $this->driver->find('ManyRight', $right2->getId());
        $this->assertEquals(1, count($machine->lefts),
            'Unexpected number of lefts in right2');
        foreach ($machine->lefts as $left) {
            $this->assertEquals($left->id, $left1->getId(),
                'Unexpected value for ID in left for right2');
        }
    }

    public function testCountOnEmptyReturnZero()
    {
        $count = $this->driver->count('Stuff');
        $this->assertEquals(0, $count);
    }

    public function testCountOnOneRowReturnsOne()
    {
            $this->driver->insert('Stuff', array('name' => 'stupid'));
        $count = $this->driver->count('Stuff');
        $this->assertEquals(1, $count);
    }
}

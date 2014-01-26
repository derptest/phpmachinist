<?php
use DerpTest\Machinist\Machinist;
use DerpTest\Machinist\Store\SqlStore;

class IntegrationTest extends PHPUnit_Framework_TestCase
{
    private $pdo;

    public function setUp()
    {
        $this->pdo = new PDO("sqlite::memory:");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `box_id` INTEGER NULL DEFAULT NULL);');
        $this->pdo->exec('create table `box` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100) );');
        $this->pdo->exec('CREATE TABLE `some_stuff` (
          `some_id` int(10)  NOT NULL,
          `stuff_id` int(10)  NOT NULL,
          `name` VARCHAR(100),
          PRIMARY KEY (`some_id`,`stuff_id`));');
        \DerpTest\Machinist\Machinist::store(SqlStore::fromPdo($this->pdo));

    }

    public function tearDown()
    {
        \DerpTest\Machinist\Machinist::reset();
        +$this->pdo->exec('DROP TABLE `stuff`;');

    }

    public function testWeCanCreateOneStuff()
    {
        Machinist::blueprint("test", "stuff", array('name' => "hello"))
            ->make();
        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals("hello", $row->name);
    }

    public function testReturnedDataHasPrimaryKeyCorrect()
    {
        $bp_row = Machinist::blueprint("test", "stuff", array('name' => "hello"))
            ->make();
        $this->assertEquals(1, $bp_row->getId());
    }

    public function testOverridesOverride()
    {
        Machinist::blueprint("test", "stuff", array('name' => "hello"));

        $row = Machinist::blueprint("test")->make(array('name' => "something else"));
        $this->assertEquals("something else", $row->name);

        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals("something else", $row->name);
    }

    public function testOverrideCallable()
    {
        Machinist::blueprint("test", "stuff", array('name' => "hello"));

        $row = Machinist::blueprint("test")->make(array('name' => function ($d) {
                return "something else";
            }));
        $this->assertEquals("something else", $row->name);

        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals("something else", $row->name);
    }

    public function testForeignKeyThing()
    {
        $box = Machinist::blueprint("box", "box", array('name' => 'square box'));

        Machinist::blueprint("stuff_in_a_box", "stuff", array(
            'name' => "hello",
            'box' => Machinist::relationship($box)->local('box_id')
        ));

        $bp_row = Machinist::blueprint('stuff_in_a_box')->make();


        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals(1, $row->box_id);
        $this->assertEquals(1, $bp_row->box->id);
        $this->assertEquals("square box", $bp_row->box->name);

    }

    public function testForeignKeyOverride()
    {
        $box = Machinist::blueprint("box", "box", array('name' => 'square box'));

        Machinist::blueprint("stuff_in_a_box", "stuff", array(
            'name' => "hello",
            'box' => Machinist::relationship($box)->local('box_id')
        ));

        $bp_row = Machinist::blueprint('stuff_in_a_box')->make(
            array('box' => array('name' => 'round box'))
        );

        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals(1, $row->box_id);
        $this->assertEquals(1, $bp_row->box->id);
        $this->assertEquals("round box", $bp_row->box->name);

    }

    public function testDefaultsTbaleToName()
    {
        Machinist::blueprint("stuff", array('name' => "whoa it might work"))
            ->make();
        $query = $this->pdo->prepare('SELECT * from stuff where id = 1');
        $query->execute(array());
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals("whoa it might work", $row->name);
    }

    public function testThatICanFindAnObject()
    {
        $box = Machinist::blueprint("box", "box", array('name' => 'square box'));

        $d = Machinist::blueprint("stuff_in_a_box", "stuff", array(
            'name' => "hello",
            'box' => Machinist::relationship($box)->local('box_id')
        ));
        $wut = $d->make(array('name' => 'dumb', 'box' => array('name' => 'my container')));
        $stuff = Machinist::blueprint("stuff_in_a_box")->find(array('name' => 'dumb'));
        $this->assertInstanceOf('\DerpTest\Machinist\Machine', $stuff[0]);
        $this->assertEquals($wut->getId(), $stuff[0]->getId());
        $this->assertEquals($wut->box->getId(), $stuff[0]->box->getId());

    }

    public function testFindOrCreateReturnsExitingObject()
    {
        $box = Machinist::blueprint("box", "box", array('name' => 'square box'));

        $d = Machinist::blueprint("stuff_in_a_box", "stuff", array(
            'name' => "hello",
            'box' => Machinist::relationship($box)->local('box_id')
        ));
        $wut = $d->make(array('name' => 'dumb', 'box' => array('name' => 'my container')));
        $stuff = Machinist::blueprint("stuff_in_a_box")->findOrCreate(array('name' => 'dumb'));
        $this->assertInstanceOf('\DerpTest\Machinist\Machine', $stuff);
        $this->assertEquals($wut->getId(), $stuff->getId());
        $this->assertEquals($wut->box->getId(), $stuff->box->getId());

    }

    public function testFindOrCreateMakesNewObject()
    {
        $box = Machinist::blueprint("box", "box", array('name' => 'square box'));

        Machinist::blueprint("stuff_in_a_box", "stuff", array(
            'name' => "hello",
            'box' => Machinist::relationship($box)->local('box_id')
        ));
        $stuff = Machinist::blueprint("stuff_in_a_box")->findOrCreate(array('name' => 'A New Name That Ihope is not there'));
        $this->assertInstanceOf('\DerpTest\Machinist\Machine', $stuff);
        $this->assertTrue(is_numeric($stuff->getId()));
        $this->assertEquals("A New Name That Ihope is not there", $stuff->name);

    }

    public function testCreatingSomeStuffWithCompoundKey()
    {
        $bp = Machinist::blueprint("some_stuff", array('name' => "awesome"));
        $d = $bp->make(array('some_id' => 1, 'stuff_id' => 2));
        $this->assertEquals("awesome", $d->name);
        $this->assertEquals(1, $d->some_id);
        $this->assertEquals(2, $d->stuff_id);
    }

    public function testFindOneOrSomething()
    {
        $bp = Machinist::blueprint("some_stuff", array('name' => "awesome"));
        $values = array('some_id' => 1, 'stuff_id' => 2);
        $d = $bp->make($values);

        $r = Machinist::blueprint('some_stuff')->findOne($values);
        $this->assertEquals($d->getId(), $r->getId());
    }

    public function testFindOrSomething()
    {
        $bp = Machinist::blueprint("some_stuff", array('name' => "awesome"));
        $values = array('some_id' => 1, 'stuff_id' => 2);
        $d = $bp->make($values);

        $r = Machinist::blueprint('some_stuff')->find($values);
        $this->assertEquals($d->getId(), $r[0]->getId());
    }
}

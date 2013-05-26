<?php
use DerpTest\Machinist\Blueprint;
use DerpTest\Machinist\Machinist;

class BlueprintTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->store = Phake::mock('\DerpTest\Machinist\Store\Store');
        $this->machinist = Phake::mock('\DerpTest\Machinist\Machinist');
        Phake::when($this->machinist)
            ->getStore(Phake::equalTo('default'))
            ->thenReturn($this->store);
    }

    public function testMakeProducesMachine()
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'col1' => 'test1',
                'col2' => 'test2'
            )
        );
        Phake::when($this->store)
            ->primaryKey(Phake::equalTo("test_table"))
            ->thenReturn("test_table_id");
        Phake::when($this->store)
            ->insert(Phake::equalTo("test_table"), Phake::equalTo(array('col1' => 'test1', 'col2' => 'test2')))
            ->thenReturn(1);
        $expected = new stdClass();
        $expected->test_table_id = 1;
        $expected->col1 = "test1";
        $expected->col2 = "test2";
        Phake::when($this->store)
            ->find(Phake::equalTo("test_table"), Phake::equalTo(1))
            ->thenReturn($expected);
        $result = $bp->make();
        $this->assertInstanceOf('\DerpTest\Machinist\Machine', $result);
        $this->assertEquals("test_table", $result->getTable());
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('test1', $result->col1);
        $this->assertEquals('test2', $result->col2);
    }

    public function testMakeOnTableWithNoPrimaryKeyProducesMachine()
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'col1' => 'test1',
                'col2' => 'test2'
            )
        );
        Phake::when($this->store)
            ->primaryKey(Phake::equalTo("test_table"))
            ->thenReturn(false);
        Phake::when($this->store)
            ->insert(Phake::equalTo("test_table"), Phake::equalTo(array('col1' => 'test1', 'col2' => 'test2')))
            ->thenReturn(0);
        $expected = new stdClass();
        $expected->test_table_id = 1;
        $expected->col1 = "test1";
        $expected->col2 = "test2";
        Phake::when($this->store)
            ->find(Phake::equalTo("test_table"), Phake::equalTo(array('col1' => 'test1', 'col2' => 'test2')))
            ->thenReturn(array($expected));
        Phake::when($this->store)
            ->columns(Phake::equalTo("test_table"))
            ->thenReturn(array('col1', 'col2'));
        $result = $bp->make();
        $this->assertInstanceOf('\DerpTest\Machinist\Machine', $result);
        $this->assertEquals("test_table", $result->getTable());
        $this->assertEquals('test1', $result->col1);
        $this->assertEquals('test2', $result->col2);
    }

    public function testMakeWithMultiLevelDefaultsMergesOverridesData()
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array('doc' => array('a' => 'aye', 'c' => 'see'))
        );
        Phake::when($this->store)
            ->find(Phake::anyParameters())
            ->thenReturn(array(new stdClass()));
        Phake::when($this->store)
            ->columns(Phake::anyParameters())
            ->thenReturn(array('doc'));

        $bp->make(array('doc' => array('a' => 'new aye', 'b' => 'bee')));

        $expected = array('doc' => array('a' => 'new aye', 'b' => 'bee', 'c' => 'see'));
        $actual = null;
        Phake::verify($this->store)->insert('test_table', Phake::capture($actual));
        $this->assertEquals($expected, $actual);
    }

    public function testCallableTableName()
    {
        $bp = new Blueprint(
            $this->machinist,
            function ($data) {
                return "test_" . $data['col1'];
            },
            array(
                'col1' => 'test1',
                'col2' => 'test2'
            )
        );
        $result = $bp->make();
        Phake::verify($this->store)->insert(Phake::equalTo("test_test1"), Phake::equalTo(array('col1' => 'test1', 'col2' => 'test2')));
        $this->assertTrue(true);

    }

    public function testWipeTellsStore()
    {
        $table = 'test_table';
        $bp = new Blueprint(
            $this->machinist,
            $table
        );

        $bp->wipe(true);
        Phake::verify($this->store)->wipe($table, true);

        // I only need to Phake::verify; but, if we don't have an assertion, PHPUnit
        // will cry
        $this->assertTrue(true);
    }

    public function testRelationshipCanBeOverridenWithMachine()
    {
        $relationship = Phake::mock('\DerpTest\Machinist\Relationship');
        Phake::when($relationship)->getLocal()->thenReturn("related_id");

        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'related' => $relationship
            )
        );
        $machine = Phake::mock('\DerpTest\Machinist\Machine');
        Phake::when($machine)->getIdColumn()->thenReturn('my_id');
        Phake::when($machine)->__get(Phake::anyParameters())->thenReturn(36);
        $actual = $bp->make(array('related' => $machine));
        Phake::verify($this->store)->insert(Phake::equalTo('test_table'), Phake::equalTo(array('related_id' => 36)));
        $this->assertTrue(true);
    }

    public static function hasRelationshipProvider()
    {
        return array(
            array(true, "some_relationship"),
            array(false, "another_column"),
            array(false, "nonexistent_column")
        );
    }

    /**
     * @dataProvider hasRelationshipProvider
     */
    public function testHasRelationship($expected, $relationship)
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'some_relationship' => Phake::mock('\DerpTest\Machinist\Relationship'),
                'another_column' => 1
            )
        );
        $this->assertEquals($expected, $bp->hasRelationship($relationship));
    }

    public function testFindsByCompoundPrimaryKey()
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'column1' => "hello",
                'column2' => "dumb",
            )
        );
        $expected = array('key1' => 8, 'key2' => 12, 'column1' => "hello", 'column2' => "dumb");
        Phake::when($this->store)->insert(
            Phake::equalTo("test_table"),
            Phake::equalTo(array('column1' => "hello", 'column2' => "dumb", "key1" => 8, 'key2' => 12))
        )->thenReturn(0);
        Phake::when($this->store)->primaryKey("test_table")->thenReturn(array('key1', 'key2'));
        Phake::when($this->store)->find(
            Phake::equalTo("test_table"),
            Phake::equalTo(array('key1' => 8, 'key2' => 12))
        )->thenReturn(array($expected));

        $actual = $bp->make(array('key1' => 8, 'key2' => 12));
        $this->assertEquals($actual->toArray(), $expected);
    }

    public function testFindsByCompoundPrimaryKeyAndSequence()
    {
        $bp = new Blueprint(
            $this->machinist,
            'test_table',
            array(
                'column1' => "hello",
                'column2' => "dumb",
            )
        );
        $expected = array('key1' => 8, 'key2' => 12, 'column1' => "hello", 'column2' => "dumb");
        Phake::when($this->store)->insert(
            Phake::equalTo("test_table"),
            Phake::equalTo(array('column1' => "hello", 'column2' => "dumb", "key1" => 8))
        )->thenReturn(12);
        Phake::when($this->store)->primaryKey("test_table")->thenReturn(array('key1', 'key2'));
        Phake::when($this->store)->find(
            Phake::equalTo("test_table"),
            Phake::equalTo(array('key1' => 8, 'key2' => 12))
        )->thenReturn(array($expected));
        $actual = $bp->make(array('key1' => 8, 'key2' => 12));
        $this->assertEquals($actual->toArray(), $expected);
    }

    public function testHasRelationShipWithNoDefaults()
    {
        $bp = new Blueprint($this->machinist, 'test_table', null);
        $this->assertFalse($bp->hasRelationship("hello"));
    }
}
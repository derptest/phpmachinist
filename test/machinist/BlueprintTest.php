<?php
use \machinist\Machinist;
use \machinist\Blueprint;
use \machinist\relationship\Relationship;
use \machinist\driver\Store;

class BlueprintTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->store = Phake::mock('\machinist\driver\Store');
		$this->machinist = Phake::mock('\machinist\Machinist');
		Phake::when($this->machinist)
			->getStore(Phake::equalTo('default'))
			->thenReturn($this->store);
	}

	public function testMakeProducesMachine() {
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
		$this->assertInstanceOf('\machinist\Machine', $result);
		$this->assertEquals("test_table", $result->getTable());
		$this->assertEquals('test_table_id', $result->getIdColumn());
		$this->assertEquals(1, $result->getId());
		$this->assertEquals('test1', $result->col1);
		$this->assertEquals('test2', $result->col2);
	}

	public function testCallableTableName() {
		$bp = new Blueprint(
			$this->machinist,
			function($data) {
				return "test_".$data['col1'];
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

	public function testWipeTellsStore() {
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

	public function testRelationshipCanBeOverridenWithMachine() {
		$relationship = Phake::mock('\machinist\relationship\Relationship');
		Phake::when($relationship)->getLocal()->thenReturn("related_id");

		$bp = new Blueprint(
			$this->machinist,
			'test_table',
			array(
				'related' => $relationship
			)
		);
		$machine = Phake::mock('\machinist\Machine');
		Phake::when($machine)->getIdColumn()->thenReturn('my_id');
		Phake::when($machine)->__get(Phake::anyParameters())->thenReturn(36);
		$actual = $bp->make(array('related' => $machine));
		Phake::verify($this->store)->insert(Phake::equalTo('test_table'), Phake::equalTo(array('related_id' => 36)));
		$this->assertTrue(true);
	}

	public static function hasRelationshipProvider() {
		return array (
			array(true, "some_relationship"),
			array(false, "another_column"),
			array(false, "nonexistent_column")
		);
	}

	/**
	 * @dataProvider hasRelationshipProvider
	 */
	public function testHasRelationship($expected, $relationship) {
		$bp = new Blueprint(
			$this->machinist,
			'test_table',
			array(
				'some_relationship' => Phake::mock('\machinist\relationship\Relationship'),
				'another_column' => 1
			)
		);
		$this->assertEquals($expected, $bp->hasRelationship($relationship));
	}
}
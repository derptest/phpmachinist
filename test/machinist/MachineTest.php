<?php
use machinist\Machine;
 
class MachineTest extends PHPUnit_Framework_TestCase {
	private $store;
	private $machine;
	public function setUp() {
		$this->store = Phake::mock('\machinist\driver\Store');
		$this->machine = new Machine($this->store, 'some_table', array('name' => "awesome"));
	}

	public function testObjectStyleAccess() {
		$this->assertEquals("awesome", $this->machine->name);
	}

	public function testCompoundKeyId() {
		$machine = new Machine($this->store, 'some_table', array('name' => "awesome", "id" => 134, 'other' => 'whut'));
		Phake::when($this->store)->primaryKey('some_table')->thenReturn(array('name','id'));

		$this->assertEquals(array("name" => "awesome", "id" => 134), $machine->getId());
	}

	public function testNonCompoundKeyId() {
		$machine = new Machine($this->store, 'some_table', array('name' => "awesome", "id" => 134, 'other' => 'whut'));
		Phake::when($this->store)->primaryKey('some_table')->thenReturn('id');

		$this->assertEquals(134, $machine->getId());
	}

	public function testNoKeyId() {
		$machine = new Machine($this->store, 'some_table', array('name' => "awesome", "id" => 134, 'other' => 'whut'));
		Phake::when($this->store)->primaryKey('some_table')->thenReturn(false);
		Phake::when($this->store)->columns('some_table')->thenReturn(array('name', 'id', 'other', 'yet_another'));

		$this->assertEquals(array('name' => "awesome", "id" => 134, 'other' => 'whut', 'yet_another' => null), $machine->getId());
	}

}

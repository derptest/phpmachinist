<?php
use machinist\Machine;
 
class MachineTest extends PHPUnit_Framework_TestCase {
	private $store;
	private $machine;
	public function setUp() {
		$this->store = Phake::mock('\machinist\driver\Store');
		$this->machine = new Machine($this->store, 'some_table', 1, array('name' => "awesome"));
	}

	public function testObjectStyleAccess() {
		$this->assertEquals("awesome", $this->machine->name);
	}

}

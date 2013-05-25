<?php

use Machinist\Machine;

class MachineTest extends PHPUnit_Framework_TestCase
{
    private $store;
    private $machine;

    public function setUp()
    {
        $this->store = Phake::mock('\Machinist\Driver\Store');
        $this->machine = new Machine($this->store, 'some_table', array('name' => "awesome"));
    }

    public function testObjectStyleAccess()
    {
        $this->assertEquals("awesome", $this->machine->name);
    }

    public function testCompoundKeyId()
    {
        $machine = new Machine($this->store, 'some_table', array('name' => "awesome", "id" => 134, 'other' => 'whut'));
        Phake::when($this->store)->primaryKey('some_table')->thenReturn(array('name', 'id'));

        $this->assertEquals(array("name" => "awesome", "id" => 134), $machine->getId());
    }

    public function testNonCompoundKeyId()
    {
        $machine = new Machine($this->store, 'some_table', array('name' => "awesome", "id" => 134, 'other' => 'whut'));
        Phake::when($this->store)->primaryKey('some_table')->thenReturn('id');

        $this->assertEquals(134, $machine->getId());
    }
}

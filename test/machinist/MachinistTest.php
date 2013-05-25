<?php

class MachinistTest extends PHPUnit_Framework_TestCase
{
    public function testBlueprintReturnsSameInstance()
    {
        $bp1 = \Machinist\Machinist::Blueprint("hello");
        $bp2 = \Machinist\Machinist::Blueprint("hello");
        $this->assertSame($bp1, $bp2);
    }

    public function testResetActuallyResets()
    {
        $bp1 = \Machinist\Machinist::Blueprint("hello");
        \Machinist\Machinist::reset();
        $bp2 = \Machinist\Machinist::Blueprint("hello");
        $this->assertNotSame($bp1, $bp2);
    }

    public function testGetBlueprintsHasAll()
    {
        $bp1 = \Machinist\Machinist::Blueprint("hello1");
        $bp2 = \Machinist\Machinist::Blueprint("hello2");
        $bps = \Machinist\Machinist::instance()->getBlueprints();
        $this->assertContains($bp1, $bps);
        $this->assertContains($bp2, $bps);
    }

    public function testWipeAllCallsWipeOnAllBlueprints()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        $machinist->wipeAll(true);
        Phake::verify($bp1)->wipe(true);
        Phake::verify($bp2)->wipe(true);

        // I only need to Phake::verify; but, if we don't have an assertion, PHPUnit
        // will cry
        $this->assertTrue(true);
    }

    public function testStaticWipeAllCall()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        \Machinist\Machinist::wipe();;

        Phake::verify($bp1)->wipe(false);
        Phake::verify($bp2)->wipe(false);
        $this->assertTrue(true);
    }

    public function testStaticWipesOne()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        \Machinist\Machinist::wipe("bp2");

        Phake::verify($bp2)->wipe(false);
        Phake::verify($bp1, Phake::never())->wipe(Phake::anyParameters());
        $this->assertTrue(true);
    }

    public function testStaticWipeAllCallWithTruncate()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        \Machinist\Machinist::wipe(true, true);

        Phake::verify($bp1)->wipe(true);
        Phake::verify($bp2)->wipe(true);
        $this->assertTrue(true);
    }

    public function testStaticWipesOneWithTruncate()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        \Machinist\Machinist::wipe("bp2", true);

        Phake::verify($bp2)->wipe(true);
        Phake::verify($bp1, Phake::never())->wipe(Phake::anyParameters());
        $this->assertTrue(true);
    }

    public function testStaticWipeAllButExclude()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $bp2 = Phake::mock('\Machinist\Blueprint');
        $bp3 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        $machinist->addBlueprint('bp2', $bp2);
        $machinist->addBlueprint('bp3', $bp3);
        \Machinist\Machinist::wipe(true, true, array('bp3'));

        Phake::verify($bp1)->wipe(true);
        Phake::verify($bp2)->wipe(true);
        Phake::verify($bp3, Phake::never())->wipe(true);
        $this->assertTrue(true);
    }

    /**
     * @expectedException Machinist\Error
     */
    public function testStaticWipeOneAndExcludeErrors()
    {
        $bp1 = Phake::mock('\Machinist\Blueprint');
        $machinist = \Machinist\Machinist::instance();
        $store = Phake::mock('\Machinist\Driver\Store');
        $machinist->Store($store);
        $machinist->addBlueprint('bp1', $bp1);
        \Machinist\Machinist::wipe('bp1', true, array('bp1'));
    }
}

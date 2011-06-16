<?php

class MachinistTest extends PHPUnit_Framework_TestCase {
	public function testBlueprintReturnsSameInstance() {
		$bp1 = \machinist\Machinist::Blueprint("hello");
		$bp2 = \machinist\Machinist::Blueprint("hello");
		$this->assertSame($bp1, $bp2);
	}

	public function testResetActuallyResets() {
		$bp1 = \machinist\Machinist::Blueprint("hello");
		\machinist\Machinist::reset();
		$bp2 = \machinist\Machinist::Blueprint("hello");
		$this->assertNotSame($bp1, $bp2);
	}

	public function testGetBlueprintsHasAll() {
		$bp1 = \machinist\Machinist::Blueprint("hello1");
		$bp2 = \machinist\Machinist::Blueprint("hello2");
		$bps = \machinist\Machinist::instance()->getBlueprints();
		$this->assertContains($bp1, $bps);
		$this->assertContains($bp2, $bps);
	}

	public function testWipeAllCallsWipeOnAllBlueprints() {
		$bp1 = Phake::mock('\machinist\Blueprint');
		$bp2 = Phake::mock('\machinist\Blueprint');
		$machinist = \machinist\Machinist::instance();
		$store = Phake::mock('\machinist\driver\Store');
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
}

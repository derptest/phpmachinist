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
}


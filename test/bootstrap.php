<?php
define('TEST_DIR', dirname(__FILE__));
define('SRC_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR);
require_once(SRC_DIR.'machinist'.DIRECTORY_SEPARATOR.'Machinist.php');
require_once(SRC_DIR.'machinist'.DIRECTORY_SEPARATOR.'behat'.DIRECTORY_SEPARATOR.'functions.php');
require_once('PHPUnit/Framework/Assert/Functions.php');
require_once('Phake.php');


class InstanceOfMatcher implements Phake_Matchers_IArgumentMatcher {
	private $expectedClass;
	public function __construct($expectedClass) {
		$this->expectedClass = $expectedClass;
	}
	/**
	 * Executes the matcher on a given argument value. Returns TRUE on a match, FALSE otherwise.
	 * @param mixed $argument
	 * @return boolean
	 */
	public function matches(&$argument) {
		return ($argument instanceof $this->expectedClass);
	}

	/**
	 * Returns a human readable description of the argument matcher
	 * @return string
	 */
	public function __toString() {
		$converter = new Phake_String_Converter();
		return "instance of {$converter->convertToString($this->expectedClass)}";
	}
}
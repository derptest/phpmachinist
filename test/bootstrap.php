<?php
define('SRC_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once(SRC_DIR . 'DerpTest' . DIRECTORY_SEPARATOR . 'Machinist' . DIRECTORY_SEPARATOR . 'Behat' . DIRECTORY_SEPARATOR . 'functions.php');
//require_once('PHPUnit/Framework/Assert/Functions.php');

//$phake_dir = VENDOR_DIR . DIRECTORY_SEPARATOR . 'phake/phake' . DIRECTORY_SEPARATOR . 'src';
//set_include_path(get_include_path() . PATH_SEPARATOR . $phake_dir);
//
//
//class InstanceOfMatcher implements Phake_Matchers_IArgumentMatcher
//{
//    private $expectedClass;
//
//    public function __construct($expectedClass)
//    {
//        $this->expectedClass = $expectedClass;
//    }
//
//    /**
//     * Executes the matcher on a given argument value. Returns TRUE on a match, FALSE otherwise.
//     * @param mixed $argument
//     * @return boolean
//     */
//    public function matches(&$argument)
//    {
//        return ($argument instanceof $this->expectedClass);
//    }
//
//    /**
//     * Returns a human readable description of the argument matcher
//     * @return string
//     */
//    public function __toString()
//    {
//        $converter = new Phake_String_Converter();
//        return "instance of {$converter->convertToString($this->expectedClass)}";
//    }
//}
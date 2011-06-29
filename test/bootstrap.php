<?php
define('TEST_DIR', dirname(__FILE__));
define('SRC_DIR', dirname(TEST_DIR).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR);
require_once(SRC_DIR.'machinist'.DIRECTORY_SEPARATOR.'Machinist.php');
require_once(SRC_DIR.'machinist'.DIRECTORY_SEPARATOR.'behat'.DIRECTORY_SEPARATOR.'functions.php');
require_once('PHPUnit/Framework/Assert/Functions.php');
require_once('Phake.php');
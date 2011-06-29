<?php
/**
 * Load and setup Machinist for Behat integration
 */
define('MACHINIST_DIR', dirname(__DIR__));
require_once(__DIR__.DIRECTORY_SEPARATOR.'Machinist.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'behat'.DIRECTORY_SEPARATOR.'functions.php');
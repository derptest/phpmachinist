<?php
/**
 * Load and setup Machinist for Behat integration
 */

namespace machinist\behat;


require_once(__DIR__.DIRECTORY_SEPARATOR.'Machinist.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'behat'.DIRECTORY_SEPARATOR.'functions.php');
// if we have context, we're prolly behat2 and we should handle accordingly
if (class_exists('\Behat\Behat\Context\BehatContext')) {
	require_once(__DIR__.DIRECTORY_SEPARATOR.'behat'.DIRECTORY_SEPARATOR.'MachinistContext.php');
}

<?php
$steps->Given('/^the following (\w+) exists:$/', function($world, $blueprint, $table) {
	\machinist\behat\functions\createMachinesFromTable($world, $blueprint, $table);
});
$steps->Given('/^there are no (\w+) machines$/', function($world, $bp) {
	\machinist\Machinist::wipe($bp, true);
});
$steps->Given('/^there are no machines$/', function($world) {
	\machinist\Machinist::instance()->wipeAll(true);
});

<?php
use \machinist\behat\functions\createMachinesFromTable;
$steps->Given('/^the following (\w+) exists:$/', function($world, $blueprint, $table) {
	createMachinesFromTable($world, $blueprint, $table);
});
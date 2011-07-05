<?php
$steps->Given('/^the following (\w+) exists:$/', function($world, $blueprint, $table) {
	\machinist\behat\functions\createMachinesFromTable($world, $blueprint, $table);
});

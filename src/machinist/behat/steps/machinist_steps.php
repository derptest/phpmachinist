<?php

$steps->Given('/^the following (\w+) exists:$/', function($world, $blueprint, $table) {
	$arr = array();
	foreach ($table->getHash() as $row) {
		$arr[] = \machinist\Machinist::Blueprint($blueprint)->make($row);
	}
	$world->$blueprint = $arr;
});
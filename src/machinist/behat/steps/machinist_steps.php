<?php

$steps->Given('/^the following (\w+) exists:$/', function($world, $blueprint, $table) {
	$arr = array();
	foreach ($table->getHash() as $row) {
		$bp = \machinist\Machinist::Blueprint($blueprint);
		$overrides = array();
		foreach($row as $key => $val) {
			if ($bp->hasRelationship($key)) {
				$matches = array();
				if (preg_match_all('/([^\s:]+)\s*:\s*([^\s:]+)/', $val, $matches, PREG_SET_ORDER)) {
					$rel_overrides = array();
					foreach ($matches as $match) {
						$rel_overrides[$match[1]] = $match[2];
					}
					$relationship = $bp->getRelationship($key);
					$overrides[$key] = $relationship->getBlueprint()->findOrCreate($rel_overrides);
				}else {
					throw new RuntimeException("Invalid relational data in step.");
				}

			} else {
				$overrides[$key] = $val;
			}
		}
		$arr[] = $bp->make($overrides);
	}
	$world->$blueprint = $arr;
});
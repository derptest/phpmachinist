<?php
namespace DerpTest\Machinist\Behat\functions;

use DerpTest\Machinist\Machinist;

function findRelationalOverrides($valueString)
{
    //regex is hard so lets just do this :p
    $values = array_map('trim', explode(',', $valueString));
    $rel_overrides = array();
    foreach ($values as $value) {
        if (preg_match_all('/([^\s:]+)\s*:\s*(.+)/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rel_overrides[$match[1]] = $match[2];
            }
        }
    }
    return $rel_overrides;
}

function createMachinesFromTable($world, $blueprint, $table)
{
    $arr = array();
    foreach ($table->getHash() as $row) {
        $bp = Machinist::Blueprint($blueprint);
        $overrides = array();
        foreach ($row as $key => $val) {
            if ($bp->hasRelationship($key)) {
                $rel_overrides = findRelationalOverrides($val);
                $relationship = $bp->getRelationship($key);
                $overrides[$key] = $relationship->getBlueprint()->findOrCreate($rel_overrides);
            } else {
                $overrides[$key] = $val;
            }
        }
        $arr[] = $bp->make($overrides);
    }
    $world->$blueprint = $arr;
}

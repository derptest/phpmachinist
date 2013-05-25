<?php
$steps->Given('/^the following (\w+) exists:$/', function ($world, $blueprint, $table) {
    \DerpTest\Machinist\Behat\functions\createMachinesFromTable($world, $blueprint, $table);
});
$steps->Given('/^there are no (\w+) machines$/', function ($world, $bp) {
    \DerpTest\Machinist\Machinist::wipe($bp, true);
});
$steps->Given('/^there are no machines$/', function ($world) {
    \DerpTest\Machinist\Machinist::instance()->wipeAll(true);
});

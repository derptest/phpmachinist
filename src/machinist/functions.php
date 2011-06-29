<?php
namespace machinist\functions;
require_once(__DIR__.DIRECTORY_SEPARATOR.'Machinist.php');
use \machinist\Machinist;
use \machinist\driver\Store;

/**
 * A bunch of functions to shorten some of the syntax for
 * doing things.
 * @param \machinist\driver\Store $store
 * @param string $name
 * @return void
 */

function setupStore(Store $store, $name = 'default') {
	return Machinist::Store($store, $name);
}

function blueprint($name, $defaults = null, $table = null, $store='default') {
	return Machinist::Blueprint($name, $defaults, $table, $store);
}

function relationship($bp) {
	return Machinist::Relationship($bp);
}
function wipe($bp = null, $truncate=false) {
	return Machinist::wipe($bp, $truncate);
}
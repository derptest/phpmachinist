<?php
namespace Machinist\Functions;
use Machinist\Driver\Store;
use Machinist\Machinist;

/**
 * A bunch of functions to shorten some of the syntax for
 * doing things.
 * @param \Machinist\Driver\Store $store
 * @param string $name
 * @return void
 */

function setupStore(Store $store, $name = 'default')
{
    return Machinist::Store($store, $name);
}

function blueprint($name, $defaults = null, $table = null, $store = 'default')
{
    return Machinist::Blueprint($name, $defaults, $table, $store);
}

function relationship($bp)
{
    return Machinist::Relationship($bp);
}

function wipe($bp = null, $truncate = false)
{
    return Machinist::wipe($bp, $truncate);
}
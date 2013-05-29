<?php
namespace DerpTest\Machinist\Store;

/**
 * Store is the base interface for all drivers providing access to data stores
 * regardles of type.
 */
interface Store
{

    /**
     * Get the primary key element(s) for the table
     *
     * @param  Name of table or collection
     * @return string|array A string value will be returned for tables with a
     * single key column.  An array of columns will be returned for a complex key.
     */
    public function primaryKey($table);

    /**
     * Insert the provided data into the named table
     *
     * @param string $table Name of table or collection
     * @param array $data Key/value pairs for data to be set in the table
     * @return mixed Primary key value
     */
    public function insert($table, $data);

    /**
     * Find all data entities matching the data provided on the table
     *
     * @param string $table Name of table or collection
     * @param mixed $data If an array is specified, the key value pairs will
     * be used as key/value parameters for a search.  If a non-array is specified,
     * it will be assumed that a primary key search is being performed and the
     * value specified will be used as the key for that search.
     * @return mixed If an array is returned, this will be a collection of data
     * entities.  Any other value returned will be assumed to be a single entity.
     */
    public function find($table, $data);

    /**
     * Count the total number of records for a table
     *
     * @param $table Name of table or collection
     * @return int Number of records
     */
    public function count($table);

    /**
     * Wipe all data from the provided table/collection
     * @param string $table Name of table or collection
     * @param bool $truncate Truncate the data.  In some databases, truncate is
     * preferred to simply deleting all rows for performance reasons.  In others,
     * it is not eve provided.  For the database that provide truncation, test
     * users may not have the appropriate rights to truncate and therefore should
     * not.
     */
    public function wipe($table, $truncate);
}

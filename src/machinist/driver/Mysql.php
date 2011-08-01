<?php
namespace machinist\driver;
use machinist\driver\SqlStore;

/**
 * MySQL Specific store support.
 */
class Mysql extends SqlStore {
   	public function primaryKey($table) {
		$stmt = $this->pdo()->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
		$results = array();
		while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
			$results[] = $row->Column_name;
		}

		return count($results) == 0 ? false : count($results) == 1 ? array_pop($results) : $results;
	}

    public function columns($table) {
        $stmt = $this->pdo()->query("DESCRIBE `$table`");
        $columns = array();
        while($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        return $columns;
    }

	public function quoteTable($table) {
		return '`'.$table.'`';
	}
	public function quoteColumn($column) {
		return '`'.$column.'`';
	}
}
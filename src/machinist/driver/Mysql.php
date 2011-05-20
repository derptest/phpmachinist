<?php
namespace machinist\driver;
use machinist\driver\SqlStore;

/**
 * MySQL Specific store support.
 */
class Mysql extends SqlStore implements Store {
   	public function primaryKey($table) {
		$stmt = $this->pdo()->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
		$result = $stmt->fetch();
		return $result['Column_name'];
	}

    public function columns($table) {
        $stmt = $this->pdo()->query("DESCRIBE `$table`");
        $columns = array();
        while($row = $stmt->fetch()) {
            $columns[] = $row['Field'];
        }
        return $columns;
    }
}
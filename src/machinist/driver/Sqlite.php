<?php
namespace machinist\driver;
use machinist\driver\SqlStore;

/**
 * SQLite Specific store support.
 */
class Sqlite extends SqlStore {
	public function primaryKey($table)
	{
		$stmt = $this->pdo()->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name=:name");
        $stmt->execute(array(':name' => $table));
		$results = array();
		foreach($stmt as $row) {
			$sql = $row['sql'];
			$matches = array();

			if (preg_match('/`*(\w+?)`*\s+\w+?\s+PRIMARY KEY/', $sql, $matches)) {
				$results[] = $matches[1];
			} elseif (preg_match('/PRIMARY KEY\s?\("?([\w`,]+?)"?\)/', $sql, $matches)) {
				$results = array_map(function($el) { return trim($el, '`'); }, explode(',', $matches[1]));
			}
		}

		return count($results) == 0 ? false : (count($results) == 1 ? array_pop($results) : $results);
	}

	public function columns($table) {
			$stmt = $this->_pdo->query("PRAGMA table_info($table)");
			$columns = array();
			while($row = $stmt->fetch()) {
					$columns[] = $row['name'];
			}
			return $columns;
	}

	/**
	 * Wipe all data in the data store for the provided table
	 * @param string $table Name of table to delete all rows
	 * @param bool $truncate SQLite does not support truncate
	 */
	public function wipe($table, $truncate) {
		return parent::wipe($table, false);
	}

	public function quoteTable($table) {
		return '"'.$table.'"';
	}
	public function quoteColumn($column) {
		return '"'.$column.'"';
	}
}

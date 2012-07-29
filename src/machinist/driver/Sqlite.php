<?php
namespace machinist\driver;
use machinist\driver\SqlStore;

/**
 * SQLite Specific store support.
 */
class Sqlite extends SqlStore {
	/**
	 * Dictionary of primary keys for tables
	 * @var array
	 */
	protected $key_dict;

	/**
	 * Dictionary of columns for tables
	 * @var array
	 */
	protected $column_dict;

	public function __construct(\PDO $pdo) {
		parent::__construct($pdo);
		$this->key_dict = array();
		$this->column_dict = array();
	}

	public function primaryKey($table)
	{
		if (!isset($this->key_dict[$table])) {
			$stmt = $this->pdo()->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name=:name");
					$stmt->execute(array(':name' => $table));
			$results = false;
			foreach($stmt as $row) {
				$sql = $row['sql'];
				$matches = array();

				if (preg_match('/`*(\w+?)`*\s+\w+?\s+PRIMARY KEY/', $sql, $matches)) {
					$results[] = $matches[1];
				} elseif (preg_match('/PRIMARY KEY\s?\(([\w`",\s?]+?)\s?\)/', $sql, $matches)) {
					$results = array_map(function($el) { return trim($el, '" `'); }, explode(',', $matches[1]));
				} else {
					$results = $this->columns($table);
				}
			}
			if (is_array($results) && count($results) == 1) {
				$results = array_pop($results);
			}
			$this->key_dict[$table] = $results;
		}
		return $this->key_dict[$table];
	}

	protected function columns($table) {
		if (!isset($this->column_dict[$table])) {
			$stmt = $this->pdo->query("PRAGMA table_info($table)");
			$columns = array();
			while($row = $stmt->fetch()) {
					$columns[] = $row['name'];
			}
			$this->column_dict[$table] = $columns;
		}
		return $this->column_dict[$table];
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

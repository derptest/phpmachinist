<?php
namespace machinist\driver;

/**
 * Should provide *most* for the vendor agnostic functionality
 * for dealing with an SQL based store.
 */
abstract class SqlStore implements Store {
	protected $pdo;

	public function __construct(\PDO $pdo) {
		$this->pdo = $pdo;
	}
	public function insert($table, $data) {
		$query = 'INSERT INTO '.$table.' ('.join(',', array_keys($data)).') VALUES('.trim(str_repeat('?,', count($data)),',').')';
		$stmt = $this->pdo()->prepare($query);
		$stmt->execute(array_values($data));
		return $this->pdo->lastInsertId();
	}

	public function find($table, $key) {
		$primary_key = $this->primaryKey($table);
		$query = $this->pdo()->prepare('SELECT * from '.$table.' WHERE '.$primary_key.' = ?');
		$query->execute(array($key));
		return $query->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * Method which should return a PDO connection for me to like do things with
	 * @return \PDO
	 */
	protected function pdo() {
		return $this->pdo;
	}

	/**
	 * Finds the correct SQLStore implementation based on a PDO connection.
	 * @static
	 * @throws \InvalidArgumentException
	 * @param \PDO $pdo
	 * @return \machinist\driver\Store
	 */
	public static function fromPdo(\PDO $pdo) {
		$driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
		switch ($driver) {
			case 'sqlite':
				require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'Sqlite.php');
				return new \machinist\driver\Sqlite($pdo);
			default:
				throw new \InvalidArgumentException("Unsupported PDO drive {$driver}.");
		}
	}

}

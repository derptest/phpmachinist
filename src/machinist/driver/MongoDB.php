<?php
namespace machinist\driver;

use MongoDBRef;
/**
 * Machinist driver for Mongo DB
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 */
class MongoDB implements Store
{
	/**
	 * @var \MongoDB 
	 */
	private $mongo_db;
	
	public function __construct(\MongoDB $mongo_db) {
		$this->mongo_db = $mongo_db;
	}
	//put your code here
	public function find($table, $data) {
		if (!is_array($data)) {
			$data = array("_id" => $data);
		}
		$found = array();
		$cursor = $this->mongo_db->selectCollection($table)->find($data);
		foreach ($cursor as $item) {
			$found[] = $this->translateDBReferences($item);
		}
		return $found;
	}

	public function insert($table, $data) {
		$this->mongo_db->selectCollection($table)->insert($data);
		return $data['_id'];
	}

	public function primaryKey($table) {
		return '_id';
	}

	public function wipe($table, $truncate) {
		if ($truncate) {
			$this->mongo_db->selectCollection($table)->drop();
		} else {
			$this->mongo_db->selectCollection($table)->remove();
		}
	}

	private function translateDBReferences(array $data) {
		$return = array();
		foreach ($data as $key => $value) {
			if (is_array($value) && isset($value['$ref'])) {
				$translated_vaue = $value['$id'];
			} elseif (is_array($value)) {
				$translated_vaue = $this->translateDBReferences($value);
			} else {
				$translated_vaue = $value;
			}
			$return[$key] = $translated_vaue;
		}
		return $return;
	}
}
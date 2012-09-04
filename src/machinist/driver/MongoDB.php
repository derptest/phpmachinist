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

	public function find($table, $data) {
		try {
			$collection = $this->mongo_db->selectCollection($table);
		} catch (\Exception $e) {
				throw new \machinist\Error(
								sprintf('An error occurred selecting the %s collection', $table),
								$e->getCode(), $e);
		}
		if (is_array($data)) {
			$found = array();
			try {
				$cursor = $collection->find($data);
			} catch (\Exception $e) {
				throw new \machinist\Error(
								sprintf('An error occurred finding documents in collection %s with search criteria of %s',
												$table, var_export($data, true)),
								$e->getCode(), $e);
			}
			foreach ($cursor as $item) {
				$found[] = (object) $this->translateDBReferences($item);
			}
		} else {
			try {
				$found = (object) $collection->findOne(array("_id" => $data));
			} catch (\Exception $e) {
				throw new \machinist\Error(
								sprintf('An error occurred finding a single document in collection %s with an _id property of %s',
												$table, (string) $data),
								$e->getCode(), $e);
			}
		}
		return $found;
	}

	public function insert($table, $data) {
		try {
			$this->mongo_db->selectCollection($table)->insert($data);
		} catch (\Exception $e) {
			throw new \machinist\Error(
							sprintf('Unable to insert data "%s" into table %s',
											var_export($data, true),
											$table), $e->getCode(), $e);
		}
		return $data['_id'];
	}

	public function primaryKey($table) {
		return '_id';
	}

	public function wipe($table, $truncate) {
		try {
			if ($truncate) {
				$this->mongo_db->selectCollection($table)->drop();
			} else {
				$this->mongo_db->selectCollection($table)->remove();
			}
		} catch (\Exception $e) {
			throw new \machinist\Error(
							sprintf('An error occurred wiping the table %s with the truncate flag set to %s',
											$table, $truncate ? 'true' : 'false'),
							$e->getCode(),
							$e);
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
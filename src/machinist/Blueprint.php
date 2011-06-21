<?php
namespace machinist;

use \machinist\relationship\Relationship;
use \machinist\Machine;

class FindException extends \Exception {

}
class Blueprint {
	private $table;
	private $defaults;
	private $machinist;
	private $store;

	public function __construct(Machinist $machinist, $table, $defaults = array(), $store='default') {
		$this->defaults = $defaults;
		$this->table = $table;
		$this->machinist = $machinist;
		$this->store = $store;
	}

	/**
	 * Does this blueprint have a column with the provided name that is a relationship?
	 * @param  $field
	 * @return bool
	 */
	public function hasRelationship($field) {
		return array_key_exists($field, $this->defaults) && $this->defaults[$field] instanceof Relationship;
	}

	public function getRelationship($field) {
		return $this->hasRelationship($field) ? $this->defaults[$field] : false;
	}

	public function make($overrides = array()) {

		$data = $this->buildData($overrides);
		$store = $this->machinist->getStore($this->store);
		$insert_data = array_filter($data, function($i) {
			return !(is_object($i) || is_array($i));
		});
		$table = $this->getTable($data);
		$id = $store->insert($table, $insert_data);
		$new_row = $store->find($table, $id);
		$machine = new \machinist\Machine($store, $table, $id, (array)$new_row);

		$related = array_filter($data, function($i) { return is_object($i); });
		foreach ($related as $k => $v) {
			$machine->set($k, $v);
		}
		return $machine;
	}

	/**
	 * Attempt to find an existing machine matching the data provided. If
	 * one is not found, then create a new one with the provided data.
	 * @param  $data
	 * @return bool|Machine
	 */
	public function findOrCreate($data) {
		$results = $this->find($data);
		if (empty($results)) {
			return $this->make($data);
		} else {
			return $results[0];
		}
	}

	/**
	 * Locate a single machine with the expected data. If anything but a single
	 * row is returned, this will throw a \machinist\FindException
	 * @throws FindException
	 * @param  $data
	 * @return
	 */
	public function findOne($data) {
		$rows = $this->find($data);
		$cnt = count($rows);
		if ($cnt == 1) {
			return $rows[0];
		}
		else {
			throw new FindException("{$cnt} rows received when one expected.");
		}
	}

	/**
	 * Attempt to find a machine by the provided data. An array of machine objects
	 * will be returned.
	 * @param  $data
	 * @return array|bool
	 */
	public function find($data) {
		$store = $this->machinist->getStore($this->store);
		$table = $this->getTable($data);
		$pk = $store->primaryKey($table);
		$return  = array();
		$rows = $store->find($table, $data);
		if (!is_array($rows)) {
			$rows = array($rows);
		}
		if (is_array($this->defaults)) {
			$relationships = array_filter($this->defaults, function($i) { return $i instanceof \machinist\relationship\Relationship; });
		} else {
			$relationships = false;
		}
		foreach ($rows as $row) {
			$id = $row->{$pk};
			$machine = new \machinist\Machine($store, $table, $id, (array)$row);
			if (!empty($relationships)) {
				foreach ($relationships as $k => $r) {
					$local = $r->getLocal();
					if (!empty($row->$local)) {
						$machine->set($k, $r->getBlueprint()->findOne($row->$local));
					}
				}
			}
			$return[] = $machine;
		}
		return $return;

	}

	public function getTable($data = array()) {
		if (is_callable($this->table)) {
			return call_user_func_array($this->table, array($data));
		} else {
			return $this->table;
		}
	}

	public function destroy() {
		unset($this->machinist);
	}

	
	/**
	 * Wipe all data in the data store from this blueprint
	 * @param bool $truncate Will perform wipe via truncate when true.
	 * Defaults to false.  The actual action performed will be based on the wipe
	 * method of a blueprint's store
	 */
	public function wipe($truncate = false) {
		$this->machinist->getStore($this->store)->wipe($this->getTable(), $truncate);
	}


	private function buildData($overrides) {
		$store = $this->machinist->getStore($this->store);
		$data = array();
		if (!empty($this->defaults)) {
			foreach ($this->defaults as $k => $v) {
				if ($v instanceof Relationship) {
					if(!array_key_exists($k, $overrides) || is_array($overrides[$k])) {
						$d = array_key_exists($k, $overrides) && is_array($overrides[$k]) ? $overrides[$k] : array();
						$new_row = $v->getBlueprint()->make($d);
						$fk = $v->getForeign();
						if (empty($fk)) {
							$fk = $new_row->getIdColumn();
						}
						$data[$k] = $new_row;
						$data[$v->getLocal()] = $new_row->{$fk};
						unset($overrides[$k]);
					} elseif(is_string($overrides[$k])) {
						$data[$k] = $store->find($v->getBlueprint()->getTable(), $overrides[$k]);
						$data[$v->getLocal()] =  $overrides[$k];
						unset($overrides[$k]);
					}elseif(array_key_exists($k, $overrides) && $overrides[$k] instanceof Machine) {
						$fk = $v->getForeign();
						if (empty($fk)) {
							$fk = $overrides[$k]->getIdColumn();
						}
						$data[$k] = $overrides[$k];
						$data[$v->getLocal()] = $overrides[$k]->{$fk};
						unset($overrides[$k]);
					}
				}elseif (is_callable($v)) {
					$data[$k] = call_user_func_array($v, array($data));
				} else {
					$data[$k] = $v;
				}
			}
		}
		foreach ($overrides as $k => $v) {

			if (is_callable($v)) {
				$data[$k] = call_user_func_array($v, array($data));
			} elseif(!$v instanceof Machine) {
				$data[$k] = $v;
			}
		}
		return $data;
	}
}

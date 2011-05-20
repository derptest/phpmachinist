<?php
namespace machinist;

use \machinist\relationship\Relationship;
 
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

	public function make($overrides = array()) {
		$data = $this->buildData($overrides);
		$store = $this->machinist->getStore($this->store);
		$insert_data = array_filter($data, function($i) {
			return !(is_object($i) || is_array($i));
		});
		$id = $store->insert($this->table, $insert_data);
		$new_row = $store->find($this->table, $id);
		$ret = (object)$new_row;
		$related = array_filter($data, function($i) { return is_object($i); });
		foreach ($related as $k => $v) {
			$ret->$k = $v;
		}

		return $ret;
	}

	public function getTable() {
		return $this->table;
	}

	public function destroy() {
		unset($this->machinist);
	}

	private function buildData($overrides) {
		$store = $this->machinist->getStore($this->store);
		$data = array();
		foreach ($this->defaults as $k => $v) {
			if ($v instanceof Relationship) {
				if(!array_key_exists($k, $overrides) || is_array($overrides[$k])) {
					$d = array_key_exists($k, $overrides) && is_array($overrides[$k]) ? $overrides[$k] : array();
					$new_row = $v->getBlueprint()->make($d);
					$fk = $v->getForeign();
					if (empty($fk)) {
						$fk = $store->primaryKey($v->getBlueprint()->getTable());
					}
					$data[$k] = $new_row;
					$data[$v->getLocal()] = $new_row->{$fk};
					unset($overrides[$k]);
				} elseif(is_string($overrides[$k])) {
					$data[$k] = $store->find($v->getBlueprint()->getTable(), $overrides[$k]);
					$data[$v->getLocal()] =  $overrides[$k];
					unset($overrides[$k]);
				}
			}elseif (is_callable($v)) {
				$data[$k] = call_user_func_array($v, array($data));
			} else {
				$data[$k] = $v;
			}
		}
		foreach ($overrides as $k => $v) {

			if (is_callable($v)) {
				$data[$k] = call_user_func_array($v, array($data));
			} else {
				$data[$k] = $v;
			}
		}
		return $data;
	}
}

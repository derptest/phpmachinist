<?php
namespace machinist;
 
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
		$id = $store->insert($this->table, $data);
		return $store->find($this->table, $id);
	}

	public function destroy() {
		unset($this->machinist);
	}

	private function buildData($overrides) {
		$data = array();
		$d = array_merge($this->defaults, $overrides);
   		foreach ($d as $k => $r) {
			if (is_callable($r)) {
				$data[$k] = call_user_func_array($r, array($data));
			} else {
				$data[$k] = $r;
			}
		}
		return $data;
	}
}

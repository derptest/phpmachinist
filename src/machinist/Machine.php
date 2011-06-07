<?php
namespace machinist;

class Machine implements \ArrayAccess,\IteratorAggregate {
	private $store;
	private $table;
	private $id;
	private $data;

	public function __construct(\machinist\driver\Store $store, $table, $id, $row_data) {
		$this->store = $store;
		$this->table = $table;
		$this->id = $id;
		$this->data = $row_data;
	}

	public function getTable() {
		return $this->table;
	}
	public function getIdColumn() {
		return $this->store->primaryKey($this->getTable());
	}
	public function getId() {
		return $this->id;
	}

	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset) {
		return array_key_exists($offset, $this->data) ? $this->data[$offset] : null;
	}
	public function offsetSet($offset, $value) {
		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset) {
		if (array_key_exists($offset, $this->data))
			unset($this->data[$offset]);
	}
	public function __get($k) {
		return $this->offsetGet($k);
	}
	public function __set($k, $v) {
		$this->offsetSet($k, $v);
	}
	public function __isset($k) {
		return $this->offsetExists($k);
	}

	public function __unset($k) {
		$this->offsetUnset($k);
	}

	public function getIterator() {
		return new ArrayIterator($this->data);
	}
	public function set($k, $v) {
		$this->offsetSet($k, $v);
	}
}

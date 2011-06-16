<?php
namespace machinist\driver;

interface Store {
	public function primaryKey($table);
	public function columns($table);
	public function insert($table, $data);
	public function find($table, $key);
	public function wipe($table, $truncate);
}

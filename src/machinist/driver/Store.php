<?php
namespace machinist\driver;

interface Store {
	public function primaryKey($table);
	public function insert($table, $data);
	public function find($table, $data);
	public function wipe($table, $truncate);
}

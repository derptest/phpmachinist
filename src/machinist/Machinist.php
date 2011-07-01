<?php
namespace machinist;
require_once(__DIR__.DIRECTORY_SEPARATOR.'Blueprint.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'Machine.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'Error.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'relationship'.DIRECTORY_SEPARATOR.'Relationship.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'driver'.DIRECTORY_SEPARATOR.'Store.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'driver'.DIRECTORY_SEPARATOR.'SqlStore.php');

use machinist\driver\Store;
/**
 * Do things.. Machinary style.
 */
class Machinist {
	private $blueprints;
	private $stores;

	public function __construct() {
		$this->blueprints = array();
		$this->stores = array();
	}

	public function addBlueprint($name, \machinist\Blueprint $bp) {
		$this->blueprints[$name] = $bp;
	}

	public function getBlueprint($name) {
		return array_key_exists($name, $this->blueprints) ? $this->blueprints[$name] : null;
	}

	/**
	 * Get all knows blueprints
	 * @return array Key/value pair associative array of blueprint name and object
	 */
	public function getBlueprints() {
		$return = array();
		foreach ($this->blueprints as $key => $value) {
			$return[$key] = $value;
		}
		return $return;
	}

	public function addStore(Store $store, $name) {
		$this->stores[$name] = $store;
	}

	/**
	 * @throws \InvalidArgumentException
	 * @param string $name
	 * @return \machinist\driver\Store
	 */
	public function getStore($name = 'default') {
		if (array_key_exists($name, $this->stores)) {
			return $this->stores[$name];
		} else {
			throw new \InvalidArgumentException("Invalid store name {$name}");
		}
	}

	public function destroy() {
		foreach ($this->blueprints as $bp) {
			$bp->destroy();
		}
		unset($this->blueprints);
		unset($this->connections);
	}

	/**
	 * Wipe all data in the data store from all blueprints
	 * @param bool $truncate Will perform wipe via truncate when true.
	 * Defaults to false.  The actual action performed will be based on the wipe
	 * method of a blueprint's store
	 */
	public function wipeAll($truncate = false, array $exclude = array()) {
		foreach ($this->blueprints as $name => $blueprint) {
			if (!in_array($name, $exclude)) {
				$blueprint->wipe($truncate);
			}
		}
	}

	/**
	 * Will create a new blueprint if one does not exist with the provided name. Otherwise  the existing one will
	 * be returned. If no table name is provided, the name of the blueprint is used as the name. The default fields
	 * will only be used in the case of a new blueprint.
	 * @static
	 * @param  $name
	 * @param null $defaults
 * 	 * @param null $table
	 * @return Blueprint
	 */
	public static function Blueprint($name, $defaults = null, $table = null, $store='default') {
		$me = self::instance();
		$bp = $me->getBlueprint($name);

		// for compatibility switch the arguments if they're provided backwards
		if (!is_array($defaults) && is_array($table)) {
			$b = $defaults;
			$defaults = $table;
			$table = $b;
		}
		if (!$bp instanceof \machinist\Blueprint) {
			if (empty($table)) {
				$table = $name;
			}
			$bp = new \machinist\Blueprint($me, $table, is_array($defaults) ? $defaults : array(), $store);
			self::instance()->addBlueprint($name, $bp);
		}

		return $bp;
	}

	public static function Relationship($bp) {
		$me = self::instance();
		if (is_string($bp)) {
			$bp = $me->getBlueprint($bp);
		}
		if (!$bp instanceof \machinist\Blueprint) {
			throw new \InvalidArgumentException("Invalid blue print {$bp}");
		}
		return new \machinist\relationship\Relationship($bp);
	}

	public static function Store(Store $store, $name = 'default') {
		self::instance()->addStore($store, $name);
	}


	public static function reset() {
		self::instance()->destroy();
		self::$instance = null;
	}


	private static $instance;

	/**
	 * Returns a singleton instanceof th machinist
	 * @static
	 * @return \machinist\Machinist
	 */
	public static function instance() {
		if (!self::$instance instanceof self) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wipes blueprints specified in $bp or all blueprints if $b is true or null. If wiping all blueprints
	 * you can exclude blueprints by passing them as an array of names into $exclude.
	 *
	 * @param array $exclude an array of blueprint names that should be excluded when wiping all blueprints
	 */
	public static function wipe($bp = null, $truncate=false, array $exclude = array()) {
		if (is_null($bp) || $bp === true) {
			self::instance()->wipeAll($truncate, $exclude);
		} elseif (self::instance()->getBlueprint($bp) !== null) {
			if (in_array($bp, $exclude)) {
				throw new Error("Cannot wipe blueprint ($bp) when it is in excluded list.");
			}
			self::instance()->getBlueprint($bp)->wipe($truncate);
		}
	}

	/**
	 * Recursively load all PHP files in a directory. Seems this  is common for Machinist projects
	 * so I'm putting it here for convenience.
	 * @static
	 * @param  $dir
	 * @return void
	 */
	public static function load($dir) {
		$files = glob($dir.DIRECTORY_SEPARATOR."*");
		foreach ($files as $file) {
			if (is_file($file) && preg_match('/\.php$/', $file)) {
				require_once($file);
			} elseif(is_dir($file) && basename($file) != ".." && basename($file) != ".") {
				self::load($dir);
			}
		}
	}

}

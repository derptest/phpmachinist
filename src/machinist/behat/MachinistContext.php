<?php
namespace machinist\behat;
require_once(__DIR__.DIRECTORY_SEPARATOR.'functions.php');
use Behat\Behat\Context\BehatContext,
	Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;

use \machinist\Machinist,
	\machinist\driver\SqlStore,
	\machinist\behat\functions\createMachinesFromTable;

use PDO;

class MachinistContext extends BehatContext {
	private $machine;
	public function __construct($parameters, $machine = null) {
		if (is_null($machine)) {
			$machine = Machinist::instance();
		}
		if (!$machine instanceof \machinist\Machinist) {
			throw new \InvalidArgumentException("Machine must be of type \\machinist\\Machinist.");
		}
		$this->machine = $machine;
		if (is_array($parameters) && array_key_exists('database', $parameters)) {
			$this->initializeStores($parameters['database']);
		}
	}

	protected function getMachine() {
		return is_null($this->machine) ? Machinist::instance() : $this->machine;
	}

	protected function initializeStores($databases) {
		$set_default = false;
		foreach ($databases as $name => $db) {
			if (array_key_exists('driver', $db)) {
				$store = new $db['driver']($db);
			}
			else {
				$user = empty($db['user']) ? 'root' : $db['user'];
				$password = empty($db['password']) ? null : $db['password'];
				$store = SqlStore::fromPdo(new PDO($db['dsn'], $user, $password));
			}
			$this->getMachine()->Store($store, $name);
			if ((array_key_exists('default', $db) && $db['default']) || !$set_default) {
				$set_default = true;
				$this->getMachine()->Store($store);
			}
		}
	}

	/**
	 *@Given /^there are no (\w+) machines$/
	 */
	public function wipeOneMachines($machine) {
		$this->getMachine()->wipe($machine, true);
	}

	/**
	 * @Given /^there are no machines$/
	 */
	public function wipeAll() {
		$this->getMachine()->wipeAll(true);
	}

	/**
	 *@Given /^the following (.+) exists:$/
	 */
	public function createMachinesStep($blueprint, $table = null) {
		if (is_null($table)) {
			$table = new TableNode();
		}
   		\machinist\behat\functions\createMachinesFromTable($this, $blueprint, $table);
	}
}
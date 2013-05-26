<?php
namespace DerpTest\Machinist\Store;

/**
 * Should provide *most* for the vendor agnostic functionality
 * for dealing with an SQL based store.
 */
abstract class SqlStore implements Store
{
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert($table, $data)
    {
        $columns = array_map(array($this, 'quoteColumn'), array_keys($data));

        $query = 'INSERT INTO ' . $this->quoteTable($table) . ' (' . join(',', $columns) . ') VALUES(' . trim(str_repeat('?,', count($data)), ',') . ')';
        $stmt = $this->pdo()->prepare($query);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function find($table, $data)
    {
        if (!is_array($data)) {
            return $this->findByPrimarykey($table, $data);
        } else {
            return $this->findByColumnValues($table, $data);
        }
    }

    public function findByColumnValues($table, $data)
    {
        $where = array();
        $values = array();
        foreach ($data as $key => $v) {
            $where[] = $this->quoteColumn($key) . " = ?";
            $values[] = $v;
        }
        $query = $this->pdo()->prepare('SELECT * from ' . $this->quoteTable($table) . ' WHERE ' . join(' AND ', $where));
        $query->execute($values);
        return $query->fetchAll(\PDO::FETCH_OBJ);

    }

    protected function findByPrimarykey($table, $key)
    {
        $primary_key = $this->primaryKey($table);
        $query = $this->pdo()->prepare('SELECT * from ' . $this->quoteTable($table) . ' WHERE ' . $this->quoteColumn($primary_key) . ' = ?');
        $query->execute(array($key));
        return $query->fetch(\PDO::FETCH_OBJ);

    }

    /**
     * Wipe all data in the data store for the provided table
     * @param string $table Name of table to remove all data
     * @param bool $truncate Will use truncate to delete data from table when set
     * to true
     */
    public function wipe($table, $truncate)
    {
        if ($truncate) {
            $query = 'TRUNCATE TABLE ' . $this->quoteTable($table);
        } else {
            $query = 'DELETE FROM ' . $this->quoteTable($table);
        }
        return $this->pdo->exec($query);
    }

    /**
     * Method which should return a PDO connection for me to like do things with
     * @return \PDO
     */
    protected function pdo()
    {
        return $this->pdo;
    }

    /**
     * Finds the correct SQLStore implementation based on a PDO connection.
     * @static
     * @throws \InvalidArgumentException
     * @param \PDO $pdo
     * @return \DerpTest\Machinist\Store\Store
     */
    public static function fromPdo(\PDO $pdo)
    {
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        switch ($driver) {
            case 'sqlite':
                require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Sqlite.php');
                return new \DerpTest\Machinist\Store\Sqlite($pdo);
            case 'mysql':
                require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Mysql.php');
                return new \DerpTest\Machinist\Store\Mysql($pdo);
            default:
                throw new \InvalidArgumentException("Unsupported PDO drive {$driver}.");
        }
    }

    abstract public function quoteTable($table);

    abstract public function quoteColumn($column);

    public function quoteValue($value)
    {
        return $this->pdo()->quote($value);
    }

}

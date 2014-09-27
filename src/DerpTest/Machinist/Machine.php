<?php
namespace DerpTest\Machinist;

use DerpTest\Machinist\Store\StoreInterface;

class Machine implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var Store\Store
     */
    private $store;

    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $data;

    /**
     * @param StoreInterface $store
     * @param string $table
     * @param array $row_data
     */
    public function __construct(StoreInterface $store, $table, $row_data)
    {
        $this->store = $store;
        $this->table = $table;
        $this->data = $row_data;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     *Returns either the string if it's a single column as the primary key, or it will return
     * an array of strings each representing one piece of a compound primary key, or it will
     * return all columns if there is no primary key with the assumption that each row is unique.
     * @return string/array
     */
    public function getIdColumn()
    {
        $key = $this->store->primaryKey($this->getTable());
        return $key;
    }

    /**
     * This will return a single string if it's a simple prmary key or an associative
     * array when the table has a compound key. Each key in the array will be the
     * name of the column in the key, with the value representing the value of that column.
     * @return string|array
     */
    public function getId()
    {
        $columns = $this->getIdColumn();
        if (is_array($columns)) {
            $return = array();
            foreach ($columns as $key) {
                $return[$key] = $this->offsetGet($key);
            }
            return $return;
        } else {
            return $this->offsetGet($columns);
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->data) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->data))
            unset($this->data[$offset]);
    }

    public function __get($k)
    {
        return $this->offsetGet($k);
    }

    public function __set($k, $v)
    {
        $this->offsetSet($k, $v);
    }

    public function __isset($k)
    {
        return $this->offsetExists($k);
    }

    public function __unset($k)
    {
        $this->offsetUnset($k);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function set($k, $v)
    {
        $this->offsetSet($k, $v);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = array();
        foreach ($this as $k => $v) {
            $ret[$k] = $v instanceof self ? $v->toArray() : $v;
        }
        return $ret;
    }

}

<?php
namespace DerpTest\Machinist\Store;

use DerpTest\Machinist\Error;

/**
 * Machinist driver for Mongo DB
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 */
class MongoDB implements Store
{
    /**
     * @var \MongoDB
     */
    private $mongoDB;

    public function __construct(\MongoDB $mongoDB)
    {
        $this->mongoDB = $mongoDB;
    }

    public function find($table, $data)
    {
        try {
            $collection = $this->mongoDB->selectCollection($table);
        } catch (\Exception $e) {
            throw new Error(
                sprintf('An error occurred selecting the %s collection', $table),
                $e->getCode(), $e);
        }
        if (is_array($data)) {
            $found = array();
            try {
                $cursor = $collection->find($data);
            } catch (\Exception $e) {
                throw new Error(
                    sprintf('An error occurred finding documents in collection %s with search criteria of %s',
                        $table, var_export($data, true)),
                    $e->getCode(), $e);
            }
            foreach ($cursor as $item) {
                $found[] = (object)$this->translateDBReferences($item);
            }
        } else {
            try {
                $found = (object)$collection->findOne(array("_id" => $data));
            } catch (\Exception $e) {
                throw new Error(
                    sprintf('An error occurred finding a single document in collection %s with an _id property of %s',
                        $table, (string)$data),
                    $e->getCode(), $e);
            }
        }
        return $found;
    }

    private function translateDBReferences(array $data)
    {
        $return = array();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['$ref'])) {
                $translatedValue = $value['$id'];
            } elseif (is_array($value)) {
                $translatedValue = $this->translateDBReferences($value);
            } else {
                $translatedValue = $value;
            }
            $return[$key] = $translatedValue;
        }
        return $return;
    }

    public function insert($table, $data)
    {
        try {
            $this->mongoDB->selectCollection($table)->insert($data);
        } catch (\Exception $e) {
            throw new Error(
                sprintf('Unable to insert data "%s" into table %s',
                    var_export($data, true),
                    $table), $e->getCode(), $e);
        }
        return $data['_id'];
    }

    public function primaryKey($table)
    {
        return '_id';
    }

    public function wipe($table, $truncate)
    {
        try {
            if ($truncate) {
                $this->mongoDB->selectCollection($table)->drop();
            } else {
                $this->mongoDB->selectCollection($table)->remove(array());
            }
        } catch (\Exception $e) {
            throw new Error(
                sprintf('An error occurred wiping the table %s with the truncate flag set to %s',
                    $table, $truncate ? 'true' : 'false'),
                $e->getCode(),
                $e);
        }
    }

    /**
     * Count the total number of records for a table
     *
     * @param $table Name of table or collection
     * @return int Number of records
     */
    public function count($table)
    {
        return $this->mongoDB->selectCollection($table)->count();
    }


}
<?php
namespace machinist\driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Doctrine 2 driver for PHP Machinist
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 */
class Doctrine implements Store
{
	/**
	 * @var type 
	 */
	private $_namespaces;
	private $_em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em Doctrine 2 Entity Manager
	 * @param array $namespaces Namespaces for managed entities to allow for short
	 * names during testing
	 */
	public function __construct(EntityManager $em, array $namespaces = array()) {
		$this->_em = $em;
		$this->_namespaces = $namespaces;
	}
	
	public function columns($table) {
		$class_name = $this->resolveEntityName($table);
		$fields = $this->_em->getClassMetadata($class_name)->getFieldNames();
		return $fields;
	}

	public function find($table, $data) {
		$class_name = $this->resolveEntityName($table);
		$repo = $this->_em->getRepository($class_name);
		$qb = $repo->createQueryBuilder('e')->addSelect('e');
		if (is_array($data)) {
			$args = array();
			foreach ($data as $key => $value) {
				$qb->andWhere("e.{$key} = :{$key}");
			}
			$args = $data;
		} else {
			$key = $this->primaryKey($class_name);
			$qb->andWhere("e.{$key} = :id");
			$args = array('id' => $data);
		}
		$result = $qb->getQuery()->execute($args, Query::HYDRATE_SIMPLEOBJECT);
		$response = array();
		foreach ($result as $entity) {
			$response[] = $this->convertSimpleEntityToStdClass($entity);
		}
		if (!is_array($data) && count($response) === 1) {
			$response = array_pop($response);
		}
		return $response;
	}

	public function insert($table, $data) {
		$class_name = $this->resolveEntityName($table);
		$entity = new $class_name();
		$meta = $this->_em->getClassMetadata($class_name);
		foreach ($data as $field => $value) {
			$meta->setFieldValue($entity, $field, $value);
		}
		$this->_em->persist($entity);
		$this->_em->flush();
		$id = $meta->getIdentifierValues($entity);
		if (is_array($id) && count($id) === 1) {
			$count = array_pop($id);
		}
		return $count;
	}

	public function primaryKey($table) {
		$class_name = $this->resolveEntityName($table);
		$fields = $this->_em->getClassMetadata($class_name)
						->getIdentifierFieldNames();
		if (is_array($fields) && count($fields) === 0) {
			$key = null;
		} else if (is_array($fields) && count($fields) === 1) {
			$key = array_pop($fields);
		} else {
			$key = $fields;
		}
		return $key;
	}

	public function wipe($table, $truncate) {
		$class_name = $this->resolveEntityName($table);
		if ($truncate) {
			$meta = $this->_em->getClassMetadata($class_name);
			$table = $meta->getTableName();
			$conn = $this->_em->getConnection();
			$sql = $conn->getDatabasePlatform()->getTruncateTableSQL($table);
			$conn->exec($sql);
		} else {
			$this->_em->getRepository($class_name)
							->createQueryBuilder('e')
							->delete()
							->getQuery()
							->execute();
		}
		$this->_em->clear($class_name);
	}

	protected function resolveEntityName($table) {
		$class_name = null;
		if (\class_exists($table)) {
					$class_name = $table;
		}
		foreach ($this->_namespaces as $namespace) {
			$test = sprintf('\\%s\\%s', $namespace, $table);
			if (\class_exists($test)) {
				$class_name = $test;
				break;
			}
		}
		if (is_null($class_name)) {
			if (empty($this->_namespaces)) {
				$namespaces = ' with no namspaces provided';
			} else {
				$namespaces = ' with the following namspaces: '
				. implode(', ', $this->_namespaces);
			}
			$msg = 'Unable to locate entity ' . $table . $namespaces;
			throw new \InvalidArgumentException($msg);
		}
		return $class_name;
	}

	protected function convertSimpleEntityToStdClass($entity) {
		$object = new \stdClass();
		$meta = $this->_em->getClassMetadata(\get_class($entity));
		foreach ($meta->getFieldNames() as $field) {
			$object->{$field} = $meta->getFieldValue($entity, $field);
		}
		return $object;
	}
}